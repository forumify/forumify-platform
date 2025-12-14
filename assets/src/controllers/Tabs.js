import { Controller } from '@hotwired/stimulus';

export class Tabs extends Controller {
  static targets = ['tabs', 'tabPanels'];

  connect() {
    this.hideAllTabPanels();

    for (const tab of this.tabsTarget.children) {
      tab.addEventListener('click', (event) => {
        this.handleTabClicked(event.target);
      });
      tab.role = 'tab';
      tab.ariaControls = tab.dataset.tabId;
    }

    for (const tabPanel of this.tabPanelsTarget.children) {
      tabPanel.role = 'tabpanel';
    }

    this.selectFirstTab();
    this.tabPanelsTarget.classList.remove('d-none');
  }

  hideAllTabPanels() {
    for (const tab of this.tabsTarget.children) {
      tab.classList.remove('active');
      tab.ariaSelected = false;
    }

    for (const tabPanel of this.tabPanelsTarget.children) {
      tabPanel.classList.add('d-none');
    }
  }

  handleTabClicked(tab) {
    this.hideAllTabPanels();

    tab.ariaSelected = true;
    tab.classList.add('active');

    const selectedTabId = tab.dataset.tabId;
    const selectedTabBody = this.tabPanelsTarget.querySelector(`#${selectedTabId}`);
    selectedTabBody.classList.remove('d-none');
  }

  selectFirstTab() {
    this.handleTabClicked(this.tabsTarget.firstElementChild);
  }
}
