<<<<<<< Updated upstream
function showPlots(voice) {
    // yeah.. i know its kinda hacky
    console.log(voice);
    if (voice.indexOf('vitals') !== -1 || voice.indexOf('plot') !== -1) {
        $('[data-toggle="tab"][href="#vital-hex"]').trigger('click');
    } else if (voice.indexOf('bmi') !== -1 || voice.indexOf('BMI') !== -1 || voice.indexOf('chart') !== -1) {
        $('[data-toggle="tab"][href="#bmi-scatter"]').trigger('click');
    } else if (voice.indexOf('cat') !== -1) {
        $('#cat_img').animate({
            bottom: '-30px'
        });
        // send cat back
        setTimeout(function() {
            $('#cat_img').animate({
                bottom: '-500px'
            });
        }, 3500);
    }
}
=======
function showPlots(voice) {
    // yeah.. i know its kinda hacky
    console.log(voice);
    if (voice.indexOf('vitals') !== -1 || voice.indexOf('plot') !== -1) {
        $('[data-toggle="tab"][href="#vital-hex"]').trigger('click');
    } else if (voice.indexOf('bmi') !== -1 || voice.indexOf('BMI') !== -1 || voice.indexOf('chart') !== -1) {
        $('[data-toggle="tab"][href="#bmi-scatter"]').trigger('click');
    } else if (voice.indexOf('cat') !== -1) {
        $('#cat_img').animate({
            bottom: '-30px'
        });
        // send cat back
        setTimeout(function() {
            $('#cat_img').animate({
                bottom: '-500px'
            });
        }, 3500);
    }
}
>>>>>>> Stashed changes
