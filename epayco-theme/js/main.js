(function($) {

 
           $.getJSON("https://api.ipify.org?format=json", 
                                          function(data) { 
  
            // Setting text of element P with id 
       
            $("#ip").html(data.ip); 
        }); 
      var lang = $("#lang").text();
  if (lang=="es") {
var prev='Regresar';
var next= 'Continuar';
var finish='Pagar';
  }else{
   var prev='Prev';
var next= 'Next';
var finish='Pay'; 
  }
    var form = $("#signup-form");
    form.steps({
        headerTag: "h3",
        bodyTag: "fieldset",
        transitionEffect: "fade",
        labels: {
            previous : prev,
            next : next,
            finish : finish,
            current : ''
        },
        titleTemplate : '<h3 class="title">#title#</h3>',
        onFinished: function (event, currentIndex)
        {
    event.preventDefault();

    //captura el contenido del formulario
    var numero_documento=document.getElementById('num_doc').value;
     var nombre_f2=document.getElementById('name').value;

     var cardNumber=document.getElementById('cardnumber').value;
     var yearcardinfo=document.getElementById('exp_year').value;
    var mescardinfo=document.getElementById('exp_month').value;
    var cvcDatainfo=document.getElementById('securitycode').value;
if (!numero_documento) {
    $("#snoAlertBox").fadeIn();
   closeSnoAlertBox();

   function closeSnoAlertBox(){
window.setTimeout(function () {
  $("#snoAlertBox").fadeOut(500)
}, 3000);
}  
}else{

 if(!nombre_f2){
    $("#snoAlertBox2").fadeIn();
   closeSnoAlertBox2();

   function closeSnoAlertBox2(){
window.setTimeout(function () {
  $("#snoAlertBox2").fadeOut(500)
}, 3000);
}

 }
  else{ if(!cardNumber){
        $("#snoAlertBox3").fadeIn();
   closeSnoAlertBox3();

   function closeSnoAlertBox3(){
window.setTimeout(function () {
  $("#snoAlertBox3").fadeOut(500)
}, 3000);
}

  }else{
    if(!mescardinfo || !yearcardinfo){
    $("#snoAlertBox4").fadeIn();
   closeSnoAlertBox4();

   function closeSnoAlertBox4(){
window.setTimeout(function () {
  $("#snoAlertBox4").fadeOut(500)
}, 3000);
}


    }else{
      if(!cvcDatainfo){

      }else{

    var $form = $(this);
         
            var key = $("#mostrardatos3").text();

       
            ePayco.setPublicKey(key);

                var token = ePayco.token.create($form, function(error, token) {
                    if(!error) {
                       var tokenurl=$("#tokenurl").text();
                       var nombre_f=document.getElementById('name').value;
                       var em=document.getElementById('email').value;
                       var num_d=document.getElementById('num_doc').value;
                       var te=document.getElementById('Cellphone').value;
                       var id_d=  $('select[name="doctype"] option:selected').val();
                       var p = $("#mostrardatos3").text();
                       var p_c = $("#p_c").text();
                       var lang = $("#lang").text();
                       var p2 = $("#test").text();
                       var tokenCustomer=token;
                       var myarray = $("#myarray").text(); 
                       var response = $("#response").text();
                       var confirmacion = $("#confirmacion").text();
                       var  city = $("#city").text();
                       var  ip = $("#ip").text();
                       var description=$("#descripcion_p").text();
                       var currency=$("#currency").text();
                       var amount=$("#amount").text();
                       var Direccionf=document.getElementById('adress').value;
                        var urls= tokenurl;    
                        var data={
                        "nombre":nombre_f,"Email":em,"docunumber":num_d,"telephone":te,
                        "doc":id_d,"apikey":p,"privateKey":p_c,"lenguage":lang,"test":p2,
                        "token":token,"myarray":myarray,"response":response,"confirmacion":confirmacion,
                        "city":city,"ip":ip,"description":description,"currency":currency,"amount":amount,
                        "addressd":Direccionf,}; 
$.ajax({
type:"POST",
url:urls,
data:data,
beforeSend:function(){
if(!token=="") {
$("#signup-form").hide(); 
$("#mostrardatos21").css("padding", "10%"); 
$("#mostrardatos21").html('<div class="lds-dual-ring"></div><button class="btn btn-light" disabled>Loading<div class="spinner-grow text-dark" role="status" style="width:1rem; height: 1rem;"><span class="sr-only">Loading...</span></div><div class="spinner-grow text-dark" role="status"  style="width:1rem; height: 1rem;"><span class="sr-only">Loading...</span></div><div class="spinner-grow text-dark" role="status"  style="width:1rem; height: 1rem;"><span class="sr-only">Loading...</span></div>  <div class="spinner-grow text-dark" role="status"  style="width:1rem; height: 1rem;"><span class="sr-only">Loading...</span></div><div class="spinner-grow text-dark" role="status" style="width:1rem; height: 1rem;"><span class="sr-only">Loading...</span></div></button>');
 }  else {
alert("there are something wrong! ");
$("#signup-form").hide(); 
console.log("errorr=>",error.data.description);
$("#mostrardatos21").html("ERRORR:::::::::...");
}},
success: function(datos){  
$("#mostrardatos21").css("padding", "0%"); 
$('input[type="submit"]').prop("disabled", false);
$("#mostrardatos21").html('<div class="divstyles">'+datos+'</div>');
} }); }else{ alert("there are something wrong! ");console.log(error);  }
 }); 
  }
      }
    }
  }


}
                //

            } });


    $('#country').parent().append('<ul id="newcountry" class="select-list" name="country"></ul>');
    $('#country option').each(function(){
        $('#newcountry').append('<li value="' + $(this).val() + '">'+$(this).text()+'</li>');
    });
    $('#country').remove();
    $('#newcountry').attr('id', 'country');
    $('#country li').first().addClass('init');
    $("#country").on("click", ".init", function() {
        $(this).closest("#country").children('li:not(.init)').toggle();
    });
    
    var allOptions = $("#country").children('li:not(.init)');
    $("#country").on("click", "li:not(.init)", function() {
        allOptions.removeClass('selected');
        $(this).addClass('selected');
        $("#country").children('.init').html($(this).html());
        allOptions.toggle();
    });

    $('#daily_budget').parent().append('<ul id="newdaily_budget" class="select-list" name="daily_budget"></ul>');
    $('#daily_budget option').each(function(){
        $('#newdaily_budget').append('<li value="' + $(this).val() + '">'+$(this).text()+'</li>');
    });
    $('#daily_budget').remove();
    $('#newdaily_budget').attr('id', 'daily_budget');
    $('#daily_budget li').first().addClass('init');
    $("#daily_budget").on("click", ".init", function() {
        $(this).closest("#daily_budget").children('li:not(.init)').toggle();
    });
    
    var DailyOptions = $("#daily_budget").children('li:not(.init)');
    $("#daily_budget").on("click", "li:not(.init)", function() {
        DailyOptions.removeClass('selected');
        $(this).addClass('selected');
        $("#daily_budget").children('.init').html($(this).html());
        DailyOptions.toggle();
    });
    
    
})(jQuery);
