var bouton1 = document.getElementById("monBoutong");
var bouton2 = document.getElementById("monBoutond");
var element = document.getElementById("monElementIn");
var decalage=(document.querySelectorAll('.div6').length*10)/document.querySelectorAll('.div6').length;
var translate = 10; 

bouton1.addEventListener("click", function() {
    if(translate<10) translate += decalage;
    element.style.transform = "translateX(" + translate +"%)";
    setTimeout(selectionnerMilieu, 100);
});
bouton2.addEventListener("click", function() {
    if(translate>-((document.querySelectorAll('.div6').length*10)-decalage-10)) translate -= decalage;
    element.style.transform = "translateX(" + translate +"%)";
    setTimeout(selectionnerMilieu, 100);
});

function selectionnerMilieu(){
    document.querySelectorAll('.div6').forEach(function(e) {
        e.classList.remove("milieu");
      });
    
    document.querySelectorAll('.div6')[Math.round(-(translate-10)/decalage)].classList.add("milieu");
}

selectionnerMilieu();

