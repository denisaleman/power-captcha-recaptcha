import '../css/captcha.css';

import { initV2Checkbox } from './captcha/v2-checkbox';
import { initV2Invisible } from './captcha/v2-invisible';
import { initV3 } from './captcha/v3';

// Google reCAPTCHA callbacks
window.pwrcapInitV2cbx = initV2Checkbox;
window.pwrcapInitV2inv = initV2Invisible;
window.pwrcapInitV3 = initV3;