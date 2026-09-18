import './bootstrap';

document.addEventListener('DOMContentLoaded', ()=>{
  initData();

  const page = document.body.dataset.page;
  renderNavbar(page);
  renderFooter();

  if(page && typeof Pages[page] === 'function'){
    Pages[page]();
  }
});