const burger = document.querySelector('.burger');
const nav = document.querySelector('#mobi-nav-container');

burger.addEventListener('click', () => {
    nav.classList.toggle('show-nav');
    burger.classList.toggle('toggle');
});