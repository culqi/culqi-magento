
/*MASTERCARD*/
const fnMcSonic = (success_url, json) => {
   jQuery("body").append('<mc-sonic id="mc-sonic"  clear-background></mc-sonic>');
    const time = 2000;
    document.addEventListener('sonicCompletion', onCompletion(success_url, json, time));
    let mc_component = document.getElementById("mc-sonic");
    mc_component.play();
};

const onCompletion = (success_url, json, time = 1) => {
    setTimeout(() => {
       post(success_url, json);
    }, time);
};

/*VISA*/
const fnBrandvisa = (success_url, json, baseurl) => {
    jQuery('body').html(`<div id="brand-wrapper">
        <div id="visa-sensory-branding"></div>
    </div>`);

    VisaSensoryBranding.init({}, 
    `${baseurl}/visa/VisaSensoryBrandingSDK`);

    document.getElementById('visa-sensory-branding').addEventListener('visa-sensory-branding-end', function(e) {
        setTimeout(() => {
            run_waitMe('transparent');
            onCompletion(success_url, json);
        }, 500);
    });

    jQuery('body').addClass('showVisa');
    setTimeout(function() { 
        VisaSensoryBranding.show();
    }, 100);
};