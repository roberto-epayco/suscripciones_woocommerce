

searchVisible = 0;
transparent = true;

$(document).ready(function(){

    /*  Activate the tooltips      */
    $('[rel="tooltip"]').tooltip();

   // console.log('Hace validate al form',$('.wizard-card'),$('.wizard-card').bootstrapWizard);
    // Code for the Validator
    
    //deja una referencia al metodo show del wizard para poder ser llamado externamente
    window.wizardShow = function(num) {
        window.wizardRef.bootstrapWizard('show',num);
    }

    window.wizardLocked = function(lock) {
        window.wizardRef.attr("wizardLocked",lock)
    }
    
    window.wizardRef = $('.wizard-card');

    // Wizard Initialization
    $('.wizard-card').bootstrapWizard({
        'tabClass': 'nav nav-pills',
        'nextSelector': '.btn-next',
        'previousSelector': '.btn-previous',

        onNext: function(tab, navigation, index) {

            if($('.wizard-card').attr("wizardLocked")==="true") return false;

            //console.log(tab, navigation, index);
            /*var $valid = $('.wizard-card form').valid();
          if(!$valid) {
            $validator.focusInvalid();
            return false;
          }*/
        },

        onInit : function(tab, navigation, index){

          //check number of tabs and fill the entire row
          var $total = navigation.find('li').length;
          $width = 100/$total;
          var $wizard = navigation.closest('.wizard-card');

          $display_width = $(document).width();

          if($display_width < 600 && $total > 3){
              $width = 50;
          }

          /*$('.wizard-card').attr("wiz","algo");
          console.log('INICIA WIZ',$wizard.next());*/

           navigation.find('li').css('width',$width + '%');
           $first_li = navigation.find('li:first-child a').html();
           $moving_div = $('<div class="moving-tab">' + $first_li + '</div>');
           $('.wizard-card .wizard-navigation').append($moving_div);
           refreshAnimation($wizard, index);
           $('.moving-tab').css('transition','transform 0s');
       },

        onTabClick : function(tab, navigation, index){

            if($('.wizard-card').attr("wizardLocked")==="true") return false;
            return true;

            /*console.log(tab, navigation, index);

            console.log($('.tab-pane active'));

            console.log($('.wizard-card form'));

            switch (key) {
                case value:
                    
                    break;
            
                default:
                    break;
            }

            $('.tab-pane.active').attr("data-id")

            return false;*/
            /*var $valid = $('.wizard-card form').valid();

            if(!$valid){
                return false;
            } else {
                return true;
            }*/
        },

        onTabShow: function(tab, navigation, index) {
            var $total = navigation.find('li').length;
            var $current = index+1;

            var $wizard = navigation.closest('.wizard-card');

            // If it's the last tab then hide the last button and show the finish instead
            if($current >= $total) {
                $($wizard).find('.btn-next').hide();
                $($wizard).find('.btn-finish').show();
            } else {
                $($wizard).find('.btn-next').show();
                $($wizard).find('.btn-finish').hide();
            }

            button_text = navigation.find('li:nth-child(' + $current + ') a').html();

            setTimeout(function(){
                $('.moving-tab').text(button_text);
            }, 150);

            var checkbox = $('.footer-checkbox');

            if( !index == 0 ){
                $(checkbox).css({
                    'opacity':'0',
                    'visibility':'hidden',
                    'position':'absolute'
                });
            } else {
                $(checkbox).css({
                    'opacity':'1',
                    'visibility':'visible'
                });
            }

            refreshAnimation($wizard, index);
        }
    });


    // Prepare the preview for profile picture
    $("#wizard-picture").change(function(){
        readURL(this);
    });

    $('[data-toggle="wizard-radio"]').click(function(){
        wizard = $(this).closest('.wizard-card');
        wizard.find('[data-toggle="wizard-radio"]').removeClass('active');
        $(this).addClass('active');
        $(wizard).find('[type="radio"]').removeAttr('checked');
        $(this).find('[type="radio"]').attr('checked','true');
    });

    $('[data-toggle="wizard-checkbox"]').click(function(){
        if( $(this).hasClass('active')){
            $(this).removeClass('active');
            $(this).find('[type="checkbox"]').removeAttr('checked');
        } else {
            $(this).addClass('active');
            $(this).find('[type="checkbox"]').attr('checked','true');
        }
    });

    $('.set-full-height').css('height', 'auto');

});



 //Function to show image before upload

function readURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            $('#wizardPicturePreview').attr('src', e.target.result).fadeIn('slow');
        }
        reader.readAsDataURL(input.files[0]);
    }
}

$(window).resize(function(){
    $('.wizard-card').each(function(){
        $wizard = $(this);
        index = $wizard.bootstrapWizard('currentIndex');
        refreshAnimation($wizard, index);

        $('.moving-tab').css({
            'transition': 'transform 0s'
        });
    });
});

function refreshAnimation($wizard, index){
    total_steps = $wizard.find('li').length;
    move_distance = $wizard.width() / total_steps;
    step_width = move_distance;
    move_distance *= index;

    $wizard.find('.moving-tab').css('width', step_width);
    $('.moving-tab').css({
        'transform':'translate3d(' + move_distance + 'px, 0, 0)',
        'transition': 'all 0.3s ease-out'

    });
}

function debounce(func, wait, immediate) {
  var timeout;
  return function() {
    var context = this, args = arguments;
    clearTimeout(timeout);
    timeout = setTimeout(function() {
      timeout = null;
      if (!immediate) func.apply(context, args);
    }, wait);
    if (immediate && !timeout) func.apply(context, args);
  };
};








function addEvent(element, event, fn) {
    if (element.addEventListener) {
        element.addEventListener(event, fn, false);
    } else if (element.attachEvent) {
        element.attachEvent('on' + event, fn);
    }
}

function loadScript(src, callback)
{
  var s,
      r,
      t,
      write;
      
  write = src.split("/");
  document.getElementById('loadingContent').innerHTML = '<center>'+'<img src="https://369969691f476073508a-60bf0867add971908d4f26a64519c2aa.ssl.cf5.rackcdn.com/logos/logo_epayco_200px.png" width="33%" height="33%" >'

  +'<br>'+'Cargando... '+'</center>' ;
 // alert(''); // eliminar para evitar la detencion de carga.
  r = false;
  s = document.createElement('script');
  s.type = 'text/javascript';
  s.src = src;
  s.onload = s.onreadystatechange = function() {
    if ( !r && (!this.readyState || this.readyState == 'complete') )
    {
      r = true;
      if (callback !== undefined) {
        //callback();
          setTimeout( function () { 
    $(".loading").hide();
    $("#page").show();
        },5000);
      }
    }
  };
  t = document.getElementsByTagName('script')[0];
  t.parentNode.insertBefore(s, t);
}

addEvent(window, 'load', function(){ loadScript(
        'https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js', 
      setTimeout( function () { 
    $(".loading").hide();
    $("#page").show();
        },3000) );
});
