/**
 * @param {HTMLImageElement} imgElement
 */
const uploadDataUrlImg = async (imgElement) => {
  const [meta, b64data] = imgElement.src.split(',');
  const type = meta.match(/:(.*?);/)[1];
  const ext = type.split('/')[1];

  const binData = atob(b64data);

  const bytes = new Uint8Array(binData.length);
  for (let i = 0; i < binData.length; i++) {
    bytes[i] = binData.charCodeAt(i);
  }

  const formData = new FormData();
  formData.append('file', new File([bytes], `file.${ext}`, { type }));

  try {
    const response = await fetch('/media/upload', {
      method: 'post',
      body: formData,
    });

    const data = await response.json();
    if (data.url) {
      imgElement.src = data.url;
    } else if (data.error) {
      console.error(data.error);
    }
  } catch (e) {
    console.error(e);
  }
};

/**
 * Uploads every inline data url image in the given html and replaces the src with the returned url.
 *
 * @param {string} html
 * @returns {Promise<string>}
 */
export const uploadEmbeddedImages = async (html) => {
  const dom = document.createElement('div');
  dom.innerHTML = html;

  const uploads = [];
  for (const img of dom.querySelectorAll('img')) {
    if (img.src.startsWith('data:')) {
      uploads.push(uploadDataUrlImg(img));
    }
  }

  await Promise.all(uploads);
  return dom.innerHTML;
};
