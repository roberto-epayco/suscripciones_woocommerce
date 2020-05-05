<?php
$name = $_POST['nombre'];
$email=$_POST['Email'];
$docunumber = $_POST['docunumber'];
$telephone = $_POST['telephone'];
$doc = $_POST['doc'];
$apikey=$_POST['apikey'];
$privatekey=$_POST['privateKey'];
$languaje=$_POST['lenguage'];
$test=$_POST['test'];
$token=$_POST['token'];
$myarray=$_POST['myarray'];
$myarray2=json_decode($myarray);
$response=$_POST['response'];
$confirmacion=$_POST['confirmacion'];
$city=$_POST['city'];
$ip=$_POST['ip'];
$description=$_POST['description'];
$currency=$_POST['currency'];
$amount=$_POST['amount'];
$addressd=$_POST['addressd']; 
 
if ( $test=="false") {

  $testt=false;

}else{

   $testt=true;

}



require_once 'lib/vendor/autoload.php';

$epayco = new Epayco\Epayco(array(

    "apiKey" => $apikey,

    "privateKey" => $privatekey,

    "lenguage" => $languaje,

    "test" =>  $testt

));

$customer = $epayco->customer->create(array(
    "token_card" => $token,
    "name" => $name,
    "email" => $email,
    "phone" => $telephone,
    "default" => true,
    "city" => $city,
    "address" => $addressd,
    "cell_phone"=> $telephone,
));

 $valorArray= count($myarray2);

 

 if ($valorArray>=2) {
  $factura22 = '';
 $valores_split=0;
        $valorArray2= $valorArray-1;
        for ($i=$valorArray2; $i >= 0 ; $i--) { 
            $myarr=$myarray2[$i]; 
               $id_plan=$myarr->id_plan;
  $id_plan_nombre=$myarr->name;
  $idescription_plan=$myarr->description;
  $currency_plan=$myarr->currency;
  $trial_days=$myarr->trial_days;
  $intervel_plan=$myarr->interval;
 $intervalCount_plan= $myarr->interval_count;
 $plan_amout=$myarr->amount;
   

 try{
  $planconsult = $epayco->plan->get($id_plan);
  if($planconsult->status =="true"){
    
$sub = $epayco->subscriptions->create(array(
  "id_plan" => $id_plan,
  "customer" => $customer->data->customerId,
  "token_card" => $token,
  "doc_type" => $doc,
  "doc_number" => $docunumber,
  "url_response" => $response,
  "url_confirmation" => $confirmacion,
  "address" => $addressd,
  "cell_phone"=> $telephone,
 
));

$subpay = $epayco->subscriptions->charge(array(
  "id_plan" => $id_plan,
  "customer" => $customer->data->customerId,
  "token_card" => $token,
  "doc_type" => $doc,
  "doc_number" => $docunumber,
  "ip"=>$ip,
  "cell_phone"=> $telephone,
  "url_response" => $response,
  "url_confirmation" => $confirmacion,
  "address" => $addressd,
  "city" =>$city,
 
));

$factura2 = isset($subpay->data->ref_payco) ? $subpay->data->ref_payco : 'ref_payco' ;
$factura22=$factura22.$factura2;
}else{
  
     $plan = $epayco->plan->create(array(
     "id_plan" =>  $id_plan,
     "name" =>$id_plan_nombre,
     "description" => $idescription_plan,
     "amount" => (float)$plan_amout,
     "currency" => $currency_plan,
     "interval" => $intervel_plan,
     "interval_count" => $intervalCount_plan,
     "trial_days" => $trial_days
));

$sub = $epayco->subscriptions->create(array(
  "id_plan" => $plan->data->id_plan,
  "customer" => $customer->data->customerId,
  "token_card" => $token,
  "doc_type" => $doc,
  "doc_number" => $docunumber,
  "url_response" => $response,
  "url_confirmation" => $confirmacion,
  "address" => $addressd,
  "cell_phone"=> $telephone,
 
));


$subpay = $epayco->subscriptions->charge(array(
  "id_plan" => $plan->data->id_plan,
  "customer" => $customer->data->customerId,
  "token_card" => $token,
  "doc_type" => $doc,
  "doc_number" => $docunumber,
  "ip"=>$ip,
  "cell_phone"=> $telephone,
  "url_response" => $response,
  "url_confirmation" => $confirmacion,
  "address" => $addressd,
  "city" =>$city,
));

$factura2 = isset($subpay->data->ref_payco) ? $subpay->data->ref_payco : 'ref_payco' ;

$factura22=$factura22.$factura2;
  }
  }catch(Exception $e){
      
       var_dump('ocurrio un inconveniente interno, algunos datos no son validos, por favor reintentar o comunicarce con soporte tecnico.');
  }

        };

 }else{

for ($i=$valorArray; $i >= 0 ; $i--) { 
  $myarr=$myarray2[0];
 

   $id_plan=$myarr->id_plan;
  $id_plan_nombre=$myarr->name;
  $idescription_plan=$myarr->description;
  $currency_plan=$myarr->currency;
  $trial_days=$myarr->trial_days;
  $intervel_plan=$myarr->interval;
 $intervalCount_plan= $myarr->interval_count;
 $plan_amout=$myarr->amount;
 }
   
  try{
  $planconsult = $epayco->plan->get($id_plan);

  if($planconsult->status =="true"){
    
$sub = $epayco->subscriptions->create(array(
  "id_plan" => $id_plan,
  "customer" => $customer->data->customerId,
  "token_card" => $token,
  "doc_type" => $doc,
  "doc_number" => $docunumber,
  "url_response" => $response,
  "url_confirmation" => $confirmacion,
  "address" => $addressd,
  "cell_phone"=> $telephone,
));

$subpay = $epayco->subscriptions->charge(array(
  "id_plan" => $id_plan,
  "customer" => $customer->data->customerId,
  "token_card" => $token,
  "doc_type" => $doc,
  "doc_number" => $docunumber,
  "ip"=>$ip,
  "cell_phone"=> $telephone,
  "url_response" => $response,
  "url_confirmation" => $confirmacion,
  "address" => $addressd,
  "city" =>$city,
));

$factura2 = isset($subpay->data->ref_payco) ? $subpay->data->ref_payco : 'ref_payco' ;

$factura22=$factura2;
  }else{
 
     $plan = $epayco->plan->create(array(
     "id_plan" =>  $id_plan,
     "name" =>$id_plan_nombre,
     "description" => $idescription_plan,
     "amount" => (float)$plan_amout,
     "currency" => $currency_plan,
     "interval" => $intervel_plan,
     "interval_count" => $intervalCount_plan,
     "trial_days" => $trial_days
));

$sub = $epayco->subscriptions->create(array(
  "id_plan" => $plan->data->id_plan,
  "customer" => $customer->data->customerId,
  "token_card" => $token,
  "doc_type" => $doc,
  "doc_number" => $docunumber,
  "url_response" => $response,
  "url_confirmation" => $confirmacion,
  "address" => $addressd,
  "cell_phone"=> $telephone,
));
$subpay = $epayco->subscriptions->charge(array(
  "id_plan" => $plan->data->id_plan,
  "customer" => $customer->data->customerId,
  "token_card" => $token,
  "doc_type" => $doc,
  "doc_number" => $docunumber,
  "ip"=>$ip,
  "cell_phone"=> $telephone,
  "url_response" => $response,
  "url_confirmation" => $confirmacion,
  "address" => $addressd,
  "city" =>$city,
));

$factura2 = isset($subpay->data->ref_payco) ? $subpay->data->ref_payco : 'ref_payco' ;


$factura22=$factura2;

  }
  }catch(Exception $e){

       var_dump('ocurrio un inconveniente interno, algunos datos no son validos, por favor reintentar o comunicarce con soporte tecnico.');
  }



 };
 

$factura=isset($subpay->data->factura) ? $subpay->data->factura : "";

$descripcion=isset($description) ? $description : "" ;

$valor=isset($amount) ? $amount : "";

$iva=isset($subpay->data->iva) ? $subpay->data->iva : "";

$baseiva=isset($subpay->data->baseiva) ? $subpay->data->baseiva : "";

$moneda=isset($subpay->data->moneda) ? $subpay->data->moneda : ""; 

$banco=isset($subpay->data->banco) ? $subpay->data->banco:""; 

$estado=isset($subpay->data->estado) ? $subpay->data->estado : "" ;       

$respuesta=isset($subpay->data->respuesta) ? $subpay->data->respuesta:"";

$autorizacion=isset($subpay->data->autorizacion) ? $subpay->data->autorizacion: "";  

  $recibo=isset($subpay->data->recibo) ? $subpay->data->recibo:"" ;



$fecha=isset($subpay->data->fecha) ? $subpay->data->fecha : "";

$extra=isset($subpay->data->extra1) ? $subpay->data->extra1 : "";


if (isset($subpay->data->status) && $subpay->data->status=="error") {

          $description=$subpay->data->description;
 var_dump($subpay->data);
           $errors=$subpay->data->errors;

          echo $description."<br>"; 
  
    
          die();

        }        

?>


                                    <table class="table table-condensed">

                                          <tr>

                                            <td>ref_payco</td>

                                            <td><?php echo " ".$factura22; ?> </td>               

                                          </tr>



                                   



                                          <tr>

                                              <td>descripcion </td>

                                              <td><?php echo " ".$descripcion; ?> </td> 

                                          </tr>
                                          

                                          <tr>

                                              <td>banco</td>

                                              <td><?php echo " ".$banco; ?> </td> 

                                          </tr>

                                           

                                          <tr>

                                              <td>estado</td>

                                              <td><?php echo " ".$estado; ?> </td> 

                                          </tr>

                                              

                                          <tr>

                                              <td>respuesta</td>

                                              <td><?php echo " ".$respuesta; ?> </td> 

                                          </tr>

                                          <tr>

                                              <td>autorizacion</td>

                                              <td><?php echo " ".$autorizacion; ?> </td> 

                                          </tr>

                                          <tr>

                                              <td>recibo</td>

                                              <td><?php echo " ".$recibo; ?> </td> 

                                          </tr>

                                           <tr>

                                              <td>fecha</td>

                                              <td><?php echo " ".$fecha; ?> </td> 

                                          </tr>

                                     </table>


<br>


<?php if($languaje=='es'){
?>
<a href="<?php echo $response."&ref_payco=".$factura2; ?>" class="btn btn-warning">finalizar</a>
<?php
}else{
  ?>
<a href="<?php echo $response."&ref_payco=".$factura2; ?>" class="btn btn-warning">finalizar</a>
  <?php
}
?>



<script type="text/javascript">
    $(document).ready(function(){
     
       setTimeout(function(){ 
 console.log('redirigir')
 window.location.href = "<?php echo $response."&ref_payco=".$factura2; ?>";

 
                    },1000);


   })
</script>