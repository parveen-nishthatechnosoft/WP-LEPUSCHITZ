var mobi_accordion = document.getElementsByClassName("mobi-accordion-parent");
var accordion = document.getElementsByClassName("mobi-accordion");
var i;

for (i = 0; i < mobi_accordion.length; i++) {
    mobi_accordion[i].addEventListener("click", function() {
        this.classList.toggle("accordion-active");
        var panel = this.nextElementSibling;

        if (panel.style.maxHeight) {
            panel.style.maxHeight = null;
        } else {
            panel.style.maxHeight = panel.scrollHeight + "px";
        }
    });
}

for (i = 0; i < accordion.length; i++) {
    accordion[i].addEventListener("click", function() {
        this.classList.toggle("accordion-active");
        var panel = this.nextElementSibling;

        if (panel.style.maxHeight) {
            panel.style.maxHeight = null;
        } else {
            panel.style.maxHeight = panel.scrollHeight + "px";
            panel.parentElement.style.maxHeight = (panel.parentElement.scrollHeight + panel.scrollHeight) + "px";

        }
    });
}