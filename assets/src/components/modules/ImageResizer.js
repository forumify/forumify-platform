import Quill from 'quill';

const DIRECTIONS = ['nw', 'n', 'ne', 'e', 'se', 's', 'sw', 'w'];
const MIN_SIZE = 32;

/**
 * @param {Delta} delta
 * @returns {?number}
 */
const findInsertedImage = (delta) => {
  let index = 0;
  for (const op of delta.ops) {
    if (op.retain !== undefined) {
      index += typeof op.retain === 'number' ? op.retain : 1;
    } else if (op.insert !== undefined) {
      if (op.insert.image !== undefined) {
        return index;
      }
      index += typeof op.insert === 'string' ? op.insert.length : 1;
    }
  }

  return null;
};

export class ImageResizer {
  /**
   * @param {Quill} quill
   */
  constructor(quill) {
    this.quill = quill;
    this.image = null;
    this.drag = null;
    this.frame = null;
    this.pendingSize = null;
    this.overlay = this.createOverlay();

    this.onDocumentPointerDown = this.handleDocumentPointerDown.bind(this);
    this.onReposition = this.reposition.bind(this);
    this.onPointerMove = this.handlePointerMove.bind(this);
    this.onDragEnd = this.handleDragEnd.bind(this);

    document.addEventListener('pointerdown', this.onDocumentPointerDown, true);
    window.addEventListener('resize', this.onReposition);
    quill.root.addEventListener('scroll', this.onReposition);
    quill.root.addEventListener('keydown', () => this.hide());
    quill.on(Quill.events.TEXT_CHANGE, this.handleTextChange.bind(this));
  }

  createOverlay() {
    const overlay = document.createElement('div');
    overlay.className = 'ql-image-resizer';

    for (const direction of DIRECTIONS) {
      const handle = document.createElement('span');
      handle.className = `ql-image-resizer-handle ql-image-resizer-${direction}`;
      handle.addEventListener('pointerdown', (event) => this.startDrag(event, direction));
      overlay.appendChild(handle);
    }

    return overlay;
  }

  /**
   * @param {HTMLImageElement} image
   */
  show(image) {
    this.image = image;
    this.quill.container.appendChild(this.overlay);
    this.reposition();
  }

  hide() {
    if (this.image === null) {
      return;
    }

    this.image = null;
    this.overlay.remove();
  }

  destroy() {
    this.hide();
    document.removeEventListener('pointerdown', this.onDocumentPointerDown, true);
    window.removeEventListener('resize', this.onReposition);
  }

  reposition() {
    if (this.image === null) {
      return;
    }

    const container = this.quill.container;
    const containerRect = container.getBoundingClientRect();
    const image = this.image.getBoundingClientRect();

    this.overlay.style.left = `${image.left - containerRect.left - container.clientLeft}px`;
    this.overlay.style.top = `${image.top - containerRect.top - container.clientTop}px`;
    this.overlay.style.width = `${image.width}px`;
    this.overlay.style.height = `${image.height}px`;
  }

  handleDocumentPointerDown(event) {
    if (!this.quill.container.isConnected) {
      this.destroy();
      return;
    }

    if (this.overlay.contains(event.target)) {
      return;
    }

    if (event.target instanceof HTMLImageElement && this.quill.root.contains(event.target)) {
      this.show(event.target);
      return;
    }

    this.hide();
  }

  handleTextChange(delta, _oldDelta, source) {
    if (this.image !== null && !this.image.isConnected) {
      this.hide();
    }

    this.reposition();

    if (source !== Quill.sources.USER || this.drag !== null) {
      return;
    }

    const index = findInsertedImage(delta);
    if (index === null) {
      return;
    }

    const [leaf] = this.quill.getLeaf(index + 1);
    const image = leaf?.domNode;
    if (!(image instanceof HTMLImageElement)) {
      return;
    }

    if (image.complete) {
      this.show(image);
      return;
    }

    image.addEventListener('load', () => this.show(image), { once: true });
  }

  startDrag(event, direction) {
    event.preventDefault();
    event.stopPropagation();

    const handle = event.currentTarget;
    const uniform = direction.length === 2;
    const { width, height } = this.image.getBoundingClientRect();

    this.drag = {
      handle,
      uniform,
      keepHeight: !uniform || this.image.hasAttribute('height'),
      signX: direction.includes('e') ? 1 : (direction.includes('w') ? -1 : 0),
      signY: direction.includes('s') ? 1 : (direction.includes('n') ? -1 : 0),
      startX: event.clientX,
      startY: event.clientY,
      startWidth: width,
      startHeight: height,
    };

    handle.setPointerCapture(event.pointerId);
    handle.addEventListener('pointermove', this.onPointerMove);
    handle.addEventListener('pointerup', this.onDragEnd);
    handle.addEventListener('pointercancel', this.onDragEnd);
  }

  handlePointerMove(event) {
    const { uniform, signX, signY, startX, startY, startWidth, startHeight } = this.drag;

    const deltaX = (event.clientX - startX) * signX;
    const deltaY = (event.clientY - startY) * signY;

    if (uniform) {
      const diagonal = startWidth * startWidth + startHeight * startHeight;
      const scale = 1 + (deltaX * startWidth + deltaY * startHeight) / diagonal;
      this.pendingSize = { width: startWidth * scale, height: startHeight * scale };
    } else {
      this.pendingSize = { width: startWidth + deltaX, height: startHeight + deltaY };
    }

    if (this.frame !== null) {
      return;
    }

    this.frame = window.requestAnimationFrame(() => {
      this.frame = null;
      this.applySize();
    });
  }

  handleDragEnd(event) {
    const { handle } = this.drag;
    if (handle.hasPointerCapture(event.pointerId)) {
      handle.releasePointerCapture(event.pointerId);
    }

    handle.removeEventListener('pointermove', this.onPointerMove);
    handle.removeEventListener('pointerup', this.onDragEnd);
    handle.removeEventListener('pointercancel', this.onDragEnd);

    if (this.frame !== null) {
      window.cancelAnimationFrame(this.frame);
      this.frame = null;
      this.applySize();
    }

    this.drag = null;
  }

  applySize() {
    const blot = Quill.find(this.image);
    if (!blot) {
      return;
    }

    const { uniform, keepHeight, startWidth, startHeight } = this.drag;
    const maxWidth = this.image.parentElement.clientWidth;

    let width = Math.min(Math.max(this.pendingSize.width, MIN_SIZE), maxWidth);
    let height = uniform
      ? (width / startWidth) * startHeight
      : Math.max(this.pendingSize.height, MIN_SIZE);

    if (uniform && height < MIN_SIZE) {
      height = MIN_SIZE;
      width = (height / startHeight) * startWidth;
    }

    this.quill.formatText(this.quill.getIndex(blot), 1, {
      width: String(Math.round(width)),
      height: keepHeight ? String(Math.round(height)) : null,
    }, Quill.sources.USER);

    this.reposition();
  }
}
