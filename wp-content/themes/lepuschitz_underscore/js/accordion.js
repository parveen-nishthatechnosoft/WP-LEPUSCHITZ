var accordion = document.getElementsByClassName("accordion");
var i;
var first = true;

for (i = 0; i < accordion.length; i++) {
    accordion[i].addEventListener("click", function() {

        this.classList.toggle("accordion-active");
        var panel = this.nextElementSibling;

        if (panel.style.maxHeight) {
            panel.style.maxHeight = null;
        } else {
            panel.style.maxHeight = panel.scrollHeight + "px";
        }
    });
}

if (document.getElementById("current") && document.getElementById("current").classList.contains("accordion-active") && first) {
    var current = document.getElementById("current");
    var current_panel = current.nextElementSibling;

    current_panel.style.maxHeight = current_panel.scrollHeight + "px";
    first = false;
}