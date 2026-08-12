/*
 * Configuration for where form submissions (Get A Quote, Request A Call,
 * quick chat, newsletter) actually get delivered to the site owner.
 *
 * This site is pure HTML/CSS/JS with no backend, so submissions are sent
 * straight from the browser to Web3Forms (https://web3forms.com), a free
 * third-party form-delivery service: it emails every submission to you and
 * needs no server code and no account/password, just an access key.
 *
 * To turn this on:
 *   1. Go to https://web3forms.com and enter the email address that
 *      should receive submissions. They email you an access key instantly.
 *   2. Paste that key below, between the quotes.
 *
 * Until a key is set here, submissions are only saved locally in each
 * visitor's own browser (assets/js/contact-storage.js) and will NOT reach
 * you - so nothing will be lost, but you won't be notified. 
 */
window.HEAVEN_SPACE_FORM_CONFIG = {
  web3formsAccessKey: "f0ddd4d2-5d6f-4158-9793-d6b817aa75c3"  
};
