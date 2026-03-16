/* global ts3v_display:readonly */
import { Controller } from '@hotwired/stimulus';

export class TeamspeakWidget extends Controller {
  static values = {
    tsviewerId: Number,
  };

  static targets = ['container'];

  connect() {
    this.initialized = false;
  }

  async openModal() {
    if (this.initialized) {
      return;
    }

    await new Promise((resolve) => {
      let tsviewerScript = document.getElementById('tsviewer-loader-script');
      if (tsviewerScript !== null) {
        resolve();
      }

      tsviewerScript = document.createElement('script');
      tsviewerScript.id = 'tsviewer-loader-script';
      tsviewerScript.type = 'text/javascript';
      tsviewerScript.src = 'https://static.tsviewer.com/short_expire/js/ts3viewer_loader.js';
      tsviewerScript.onload = () => resolve();

      document.body.append(tsviewerScript);
    });

    const style = window.getComputedStyle(document.body);
    const textColor = style.getPropertyValue('--c-text-primary').slice(1);

    const params = {
      ID: this.tsviewerIdValue,
      text: textColor,
      text_size: 12,
      text_family: 1,

      text_s_color: '',
      text_s_weight: 'normal',
      text_s_style: 'normal',
      text_s_variant: 'normal',
      text_s_decoration: 'none',

      text_i_color: '',
      text_i_weight: 'normal',
      text_i_style: 'normal',
      text_i_variant: 'normal',
      text_i_decoration: 'none',

      text_c_color: '',
      text_c_weight: 'normal',
      text_c_style: 'normal',
      text_c_variant: 'normal',
      text_c_decoration: 'none',

      text_u_color: '',
      text_u_weight: 'normal',
      text_u_style: 'normal',
      text_u_variant: 'normal',
      text_u_decoration: 'none',

      text_s_color_h: '',
      text_s_weight_h: 'bold',
      text_s_style_h: 'normal',
      text_s_variant_h: 'normal',
      text_s_decoration_h: 'none',

      text_i_color_h: '',
      text_i_weight_h: 'bold',
      text_i_style_h: 'normal',
      text_i_variant_h: 'normal',
      text_i_decoration_h: 'none',

      text_c_color_h: '',
      text_c_weight_h: 'normal',
      text_c_style_h: 'normal',
      text_c_variant_h: 'normal',
      text_c_decoration_h: 'none',

      text_u_color_h: '',
      text_u_weight_h: 'bold',
      text_u_style_h: 'normal',
      text_u_variant_h: 'normal',
      text_u_decoration_h: 'none',

      iconset: 'default',
    };

    const tsvUrl = `https://www.tsviewer.com/ts3viewer.php?${new URLSearchParams(params)}`;
    ts3v_display.init(tsvUrl, this.tsviewerIdValue, 100);
  }
}
