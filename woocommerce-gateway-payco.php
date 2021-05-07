<?php
/**
 * @since             1.0.0
 * @package           ePayco_Woocommerce_sub
 *
 * @wordpress-plugin
 * Plugin Name:       ePayco WooCommerce  Suscripction
 * Description:       Plugin ePayco WooCommerce.
 * Version:           4.0.x
 * Author:            ePayco
 * Author URI:        
 *Lice
 * Domain Path:       /languages
 */
/*
*Stores the location of the WordPress directory of functions, classes, and core content.
*/

   if (!defined('WPINC')) {
    die;
}
require_once(dirname(__FILE__) . '/lib/EpaycoOrders.php');
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    add_action('plugins_loaded', 'init_epayco_woocommerce_sub', 0);
   
    function init_epayco_woocommerce_sub()
    {
        if (!class_exists('WC_Payment_Gateway')) {
            return;
        }

        class WC_ePayco_sub extends WC_Payment_Gateway
        {
           public function __construct()
            {
                $this->id = 'epayco_sub';
                $this->icon = 'https://369969691f476073508a-60bf0867add971908d4f26a64519c2aa.ssl.cf5.rackcdn.com/logos/logo_epayco_200px.png';
                $this->method_title = __('ePayco Checkout Suscription', 'epayco_woocommerce_sub');
                $this->method_description = __('Suscripciones atravez de tarjetas de crédito.', 'epayco_woocommerce_sub');
                $this->order_button_text = __('Pagar', 'epayco_woocommerce_sub');
                $this->has_fields = true;
               $this->supports = [
            'subscriptions'
        ];

                $this->init_form_fields();
                $this->init_settings();
                $this->msg['message']   = "";
                $this->msg['class']     = "";
                $this->title = $this->get_option('epayco_title_sub');
                $this->currency = get_option('woocommerce_currency');
                $this->epayco_customerid_sub = $this->get_option('epayco_customerid_sub');
                $this->epayco_secretkey_sub = $this->get_option('epayco_secretkey_sub');
                $this->epayco_publickey_sub = $this->get_option('epayco_publickey_sub');
                $this->epayco_p_key_sub = $this->get_option('epayco_p_key_sub');
                $this->description = $this->get_option('description');
                $this->epayco_testmode_sub = $this->get_option('epayco_testmode_sub');
                if ($this->get_option('epayco_reduce_stock_pending_sub') !== null ) {
                    $this->epayco_reduce_stock_pending_sub = $this->get_option('epayco_reduce_stock_pending_sub');
                }else{
                     $this->epayco_reduce_stock_pending_sub = "yes";
                }
                $this->epayco_endorder_state_sub=$this->get_option('epayco_endorder_state_sub');
                $this->epayco_url_response_sub=$this->get_option('epayco_url_response_sub');
                $this->epayco_url_confirmation_sub=$this->get_option('epayco_url_confirmation_sub');
                $this->epayco_lang_sub=$this->get_option('epayco_lang_sub')?$this->get_option('epayco_lang_sub'):'es';
                add_filter('woocommerce_thankyou_order_received_text', array(&$this, 'order_received_message'), 10, 2 );
                add_action('woocommerce_order_status_changed', 'action_order_status_changed');
                add_action('ePayco_init_sub', array( $this, 'ePayco_successful_request_sub'));
                add_action('woocommerce_receipt_' . $this->id, array(&$this, 'receipt_page'));
                add_action( 'woocommerce_api_' . strtolower( get_class( $this ) ), array( $this, 'check_ePayco_response_sub' ) );
                add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
              add_action( 'init', 'woocommerce_clear_cart_url' );

                if ($this->epayco_testmode_sub == "yes") {
                    if (class_exists('WC_Logger')) {
                        $this->log = new WC_Logger();
                    } else {
                        $this->log = WC_ePayco_sub::woocommerce_instance()->logger();
                    }
                }
            }

            function order_received_message( $text, $order ) {
                if(!empty($_GET['msg'])){
                  return $text .' '.$_GET['msg'];
                }
                return $text;
            }

            public function is_valid_for_use()
            {
                return in_array(get_woocommerce_currency(), array('COP', 'USD'));
            }

            public function admin_options()
            {
                ?>
                <style>
                    tbody{

                    }
                    .epayco-table tr:not(:first-child) {
                        border-top: 1px solid #ededed;
                    }
                    .epayco-table tr th{
                            padding-left: 15px;
                            text-align: -webkit-right;
                    }
                    .epayco-table input[type="text"]{
                            padding: 8px 13px!important;
                            border-radius: 3px;
                            width: 100%!important;
                    }
                    .epayco-table .description{
                        color: #afaeae;
                    }
                    .epayco-table select{
                            padding: 8px 13px!important;
                            border-radius: 3px;
                            width: 100%!important;
                            height: 37px!important;
                    }
                    .epayco-required::before{
                        content: '* ';
                        font-size: 16px;
                        color: #F00;
                        font-weight: bold;
                    }
                </style>

                <div class="container-fluid">
                    <div class="panel panel-default" style="">
                        <img  src="https://369969691f476073508a-60bf0867add971908d4f26a64519c2aa.ssl.cf5.rackcdn.com/logos/logo_epayco_200px.png">
                        <div class="panel-heading">
                            <h3 class="panel-title"><i class="fa fa-pencil"></i>Configuración <?php _e('ePayco', 'epayco_woocommerce_sub'); ?></h3>
                        </div>
                        <div style ="color: #31708f; background-color: #d9edf7; border-color: #bce8f1;padding: 10px;border-radius: 5px;">
                            <b>Este modulo le permite aceptar pagos seguros por la plataforma de pagos ePayco</b>
                            <br>Si el cliente decide pagar por ePayco, el estado del pedido cambiara a ePayco Esperando Pago
                            <br>Cuando el pago sea Aceptado o Rechazado ePayco envia una configuracion a la tienda para cambiar el estado del pedido.
                        </div>

                        <div class="panel-body" style="padding: 15px 0;background: #fff;margin-top: 15px;border-radius: 5px;border: 1px solid #dcdcdc;border-top: 1px solid #dcdcdc;">
                                <table class="form-table epayco-table">
                                <?php
                                    if ($this->is_valid_for_use()) :
                                        $this->generate_settings_html();
                                    else :
                                        if ( is_admin() && ! defined( 'DOING_AJAX')) {
                                            echo '<div class="error"><p><strong>' . __( 'ePayco: Requiere que la moneda sea USD O COP', 'epayco-woocommerce' ) . '</strong>: ' . sprintf(__('%s', 'woocommerce-mercadopago' ), '<a href="' . admin_url() . 'admin.php?page=wc-settings&tab=general#s2id_woocommerce_currency">' . __( 'Click aquí para configurar!', 'epayco_woocommerce_sub') . '</a>' ) . '</p></div>';
                                        }
                                    endif;
                                ?>
                                </table>
                        </div>
                    </div>
                </div>

                <?php
            }
            public function init_form_fields()

            {
                $this->form_fields = array(
                    'enabled' => array(
                        'title' => __('Habilitar/Deshabilitar', 'epayco_woocommerce_sub'),
                        'type' => 'checkbox',
                        'label' => __('Habilitar ePayco Checkout', 'epayco_woocommerce_sub'),
                        'default' => 'yes'
                    ),
                    'epayco_title_sub' => array(
                        'title' => __('<span class="epayco-required">Título</span>', 'epayco_woocommerce_sub'),
                        'type' => 'text',
                        'description' => __('Corresponde al titulo que el usuario ve durante el checkout.', 'epayco_woocommerce_sub'),
                        'default' => __('Checkout ePayco (Suscripciones)', 'epayco_woocommerce_sub'),
                    ),

                    'description' => array(
                        'title' => __('<span class="epayco-required">Descripción</span>', 'epayco_woocommerce_sub'),
                        'type' => 'textarea',
                        'description' => __('Corresponde a la descripción que verá el usuaro durante el checkout', 'epayco_woocommerce_sub'),
                        'default' => __('Checkout ePayco (Suscripciones por Tarjetas de crédito)', 'epayco_woocommerce_sub'),
                    ),


                    'epayco_customerid_sub' => array(
                        'title' => __('<span class="epayco-required">P_CUST_ID_CLIENTE</span>', 'epayco_woocommerce_sub'),
                        'type' => 'text',
                        'description' => __('ID de cliente que lo identifica en ePayco. Lo puede encontrar en su panel de clientes en la opción configuración.', 'epayco_woocommerce_sub'),
                        'default' => '',
                        'placeholder' => '',
                    ),

                    'epayco_secretkey_sub' => array(
                        'title' => __('<span class="epayco-required">PRIVATE_KEY</span>', 'epayco_woocommerce_sub'),
                        'type' => 'text',
                        'description' => __('LLave para autenticar y consumir los servicios de ePayco, Proporcionado en su panel de clientes en la opción configuración.', 'epayco_woocommerce_sub'),
                        'default' => '',
                        'placeholder' => ''

                    ),

                    'epayco_publickey_sub' => array(
                        'title' => __('<span class="epayco-required">PUBLIC_KEY</span>', 'epayco_woocommerce_sub'),
                        'type' => 'text',
                        'description' => __('LLave para autenticar y consumir los servicios de ePayco, Proporcionado en su panel de clientes en la opción configuración.', 'epayco_woocommerce_sub'),
                        'default' => '',
                        'placeholder' => ''
                    ),



                     'epayco_p_key_sub' => array(
                        'title' => __('<span class="epayco-required">P_KEY</span>', 'epayco_woocommerce_sub'),
                        'type' => 'text',
                        'description' => __('LLave para firmar la información enviada y recibida de ePayco. Lo puede encontrar en su panel de clientes en la opción configuración.', 'epayco_woocommerce_sub'),
                        'default' => '',
                        'placeholder' => ''
                    ),


                    'epayco_testmode_sub' => array(
                        'title' => __('Sitio en pruebas', 'epayco_woocommerce_sub'),
                        'type' => 'checkbox',
                        'label' => __('Habilitar el modo de pruebas', 'epayco_woocommerce_sub'),
                        'description' => __('Habilite para realizar pruebas', 'epayco_woocommerce_sub'),
                        'default' => 'no',

                    ),

                    'epayco_endorder_state_sub' => array(
                        'title' => __('Estado Final del Pedido', 'epayco_woocommerce_sub'),
                        'type' => 'select',
                         'css' =>'line-height: inherit',
                        'description' => __('Seleccione el estado del pedido que se aplicará a la hora de aceptar y confirmar el pago de la orden', 'epayco_woocommerce_sub'),
                        'options' => array('epayco-processing'=>"ePayco Procesando Pago","epayco-completed"=>"ePayco Pago Completado"),
                    ),


                    'epayco_url_response_sub' => array(
                        'title' => __('Página de Respuesta', 'epayco_woocommerce_sub'),
                        'type' => 'select',
                         'css' =>'line-height: inherit',
                        'description' => __('Url de la tienda donde se redirecciona al usuario luego de pagar el pedido', 'epayco_woocommerce_sub'),
                        'options'       => $this->get_pages(__('Seleccionar pagina', 'payco-woocommerce')),
                    ),


                    'epayco_url_confirmation_sub' => array(
                        'title' => __('Página de Confirmación', 'epayco_woocommerce_sub'),
                        'type' => 'select',
                         'css' =>'line-height: inherit',
                        'description' => __('Url de la tienda donde ePayco confirma el pago', 'epayco_woocommerce_sub'),
                        'options'       => $this->get_pages(__('Seleccionar pagina', 'payco-woocommerce')),
                    ),

                    'epayco_reduce_stock_pending_sub' => array(
                        'title' => __('Reducir el stock en transacciones pendientes', 'epayco_woocommerce_sub'),
                        'type' => 'checkbox',
                        'label' => __('Habilitar', 'epayco_woocommerce_sub'),
                        'description' => __('Habilite para reducir el stock en transacciones pendientes', 'epayco_woocommerce_sub'),
                        'default' => 'yes',
                    ),


                    'epayco_lang_sub' => array(
                        'title' => __('Idioma del Checkout', 'epayco_woocommerce_sub'),
                        'type' => 'select',
                         'css' =>'line-height: inherit',
                        'description' => __('Seleccione el idioma del checkout', 'epayco_woocommerce_sub'),
                        'options' => array('es'=>"Español","en"=>"Inglés"),
                    ),

                );

            }

            /**
             * @param $order_id
             * @return array
             */

            public function process_payment($order_id)
            {
               $order = new WC_Order($order_id);
                $order->reduce_order_stock();
                if (version_compare( WOOCOMMERCE_VERSION, '2.1', '>=')) {
                    return array(
                        'result'    => 'success',
                        'redirect'  => add_query_arg('order-pay', $order->id, add_query_arg('key', $order->order_key, get_permalink(woocommerce_get_page_id('pay' ))))
                    );

                } else {
                    return array(
                        'result'    => 'success',
                        'redirect'  => add_query_arg('order', $order->id, add_query_arg('key', $order->order_key, get_permalink(woocommerce_get_page_id('pay' ))))
                    );
                }
            }



            function get_pages($title = false, $indent = true) {
                $wp_pages = get_pages('sort_column=menu_order');
                $page_list = array();
                if ($title) $page_list[] = $title;
                foreach ($wp_pages as $page) {
                    $prefix = '';
                    if ($indent) {
                        $has_parent = $page->post_parent;
                        while($has_parent) {
                            $prefix .=  ' - ';
                            $next_page = get_page($has_parent);
                            $has_parent = $next_page->post_parent;
                        }
                    }
                    $page_list[$page->ID] = $prefix . $page->post_title;
                }
                return $page_list;
            }

  private function getWooCommerceSubscriptionFromOrderId($orderId)
    {
        $subscriptions = wcs_get_subscriptions_for_order($orderId);
        return $subscriptions;
    }

 public function getPlansBySubscription(array $subscriptions)
    {
        $plans = [];
        foreach ($subscriptions as $key => $subscription){
            $total_discount = $subscription->get_total_discount();
            $order_currency = $subscription->get_currency();
            $products = $subscription->get_items();
            $product_plan = $this->getPlan($products);
            $quantity =  $product_plan['quantity'];
            $product_name = $product_plan['name'];
            $product_id = $product_plan['id'];
            $plan_code = "$product_name-$product_id";
            $plan_code = $this->currency !== $order_currency ? "$plan_code-$order_currency" : $plan_code;
            $plan_code = $quantity > 1 ? "$plan_code-$quantity" : $plan_code;
            $plan_code = $total_discount > 0 ? "$plan_code-$total_discount" : $plan_code;
            $plan_code = rtrim($plan_code, "-");

            $plans[] = array_merge(
                [
                    "id_plan" => $plan_code,
                    "name" => "Plan $plan_code",
                    "description" => "Plan $plan_code",
                    "currency" => $order_currency,
                ],
                $this->getTrialDays($subscription),
                $this->intervalAmount($subscription)
            );
        }

        return $plans;
    }

    public function getPlan($products)
    {
        $product_plan = [];

        $product_plan['name'] = '';
        $product_plan['id'] = 0;
        $product_plan['quantity'] = 0;

        foreach ($products as $product){
            $product_plan['name'] .= "{$product['name']}-";
            $product_plan['id'] .= "{$product['product_id']}-";
            $product_plan['quantity'] .=  $product['quantity'];
        }

        $product_plan['name'] = $this->cleanCharacters($product_plan['name']);

        return $product_plan;
    }

    public function intervalAmount(WC_Subscription $subscription)
    {
        return  [
            "interval" => $subscription->get_billing_period(),
            "amount" => $subscription->get_total(),
            "interval_count" => $subscription->get_billing_interval()
        ];
    }

    public function getTrialDays(WC_Subscription $subscription)
    {

        $trial_days = "0";

        $trial_start = $subscription->get_date('start');
        $trial_end = $subscription->get_date('trial_end');

        if ($trial_end > 0 )
            $trial_days = (string)(strtotime($trial_end) - strtotime($trial_start)) / (60 * 60 * 24);

        return [
            "trial_days" => $trial_days
        ];
    }
 
 public function cleanCharacters($string)
    {
        $string = str_replace(' ', '-', $string);
        $patern = '/[^A-Za-z0-9\-]/';
        return preg_replace($patern, '', $string);
    }

            /**
             * @param $order_id
             */
function action_order_status_changed( $order_id ){
    $subscriptions_ids = wcs_get_subscriptions_for_order( $order_id );
    // We get the related subscription for this order
    foreach( $subscriptions_ids as $subscription_id => $subscription_obj )
        if($subscription_obj->order->id == $order_id) break; // Stop the loop
}

            public function receipt_page($order_id)
            {
                global $woocommerce;

 $subscription = new WC_Subscription($order_id);
 $userid = wcs_get_subscription($order_id);
 $subscriptions_ids = wcs_get_subscriptions_for_order( $order_id );
  foreach( $subscriptions_ids as $subscription_id => $subscription_obj ){

        if($subscription_obj->order->id == $order_id) break; // Stop the loop
  }

                $redirect_url =get_site_url() . "/";
                $redirect_url = add_query_arg( 'wc-api', get_class( $this ), $redirect_url );
                $redirect_url = add_query_arg( 'order_id', $order_id, $redirect_url );
                 $confirm_url=get_site_url() . "/";
                $confirm_url = add_query_arg( 'wc-api', get_class( $this ), $confirm_url );
                $confirm_url = add_query_arg( 'order_id', $subscription_obj->id, $confirm_url );
                $confirm_url = $confirm_url.'&confirmation=1';

                $subscriptions = $this->getWooCommerceSubscriptionFromOrderId($order_id);
                $suscription_count=count($subscriptions);
                $plans = $this->getPlansBySubscription($subscriptions);
                $arrayName2 = array();


                foreach ($plans as $key => $value) {
 
                array_push($arrayName2,$value);

                }
                
                global $woocommerce;
   
                $datasuscription=json_encode($arrayName2);
                $amoutTotal =0;
                for ($i=$suscription_count; $i >=0 ; $i--) {
                $amoutTotal += $arrayName2[$i]["amount"];
                }

                $amountPayment=number_format($amoutTotal, 2);
                $descripcionParts = array();
                foreach ($subscription->get_items() as $product) {
                    $descripcionParts[] = $this->string_sanitize($product['name']);
                }
                $descripcion = implode(' - ', $descripcionParts);
                $nameproductd = $descripcion;
                $currency = get_woocommerce_currency();
                $testMode = $this->epayco_testmode_sub == "yes" ? "true" : "false";
                $name_billing=$subscription->get_billing_first_name().' '.$subscription->get_billing_last_name();
                $address_billing=$subscription->get_billing_address_1();
                $phone_billing=$subscription->billing_phone;
                $email_billing=$subscription->billing_email;
                $order_data = $subscription->get_data();
                $order_billing_city = $order_data['billing']['city'];
                $tax=$subscription->get_total_tax();
                $tax=number_format($tax, 2);

                if((int)$tax>0){

                $base_tax2=$subscription->get_total()-$tax;


                $base_tax=number_format($base_tax2, 2);
 
                }else{

                $base_tax=number_format($amoutTotal, 2);
                $tax=number_format(0, 2);
                }
                if (!EpaycoOrders::ifExist($order_id)) {
                    $this->restore_order_stock($order_id);
                    EpaycoOrders::create($order_id,1);
                }


               $ruta0=plugin_dir_url(__FILE__) .'payment-card-checkout/card.css';
               $ruta1=plugin_dir_url(__FILE__) .'epayco-theme/css/style.css';
               $ruta2=plugin_dir_url(__FILE__) .'epayco-theme/assets/css/bootstrap.min.css';
               $ruta3=plugin_dir_url(__FILE__) .'payment-card-checkout/card.js';
               $ruta4='https://cdnjs.cloudflare.com/ajax/libs/imask/3.4.0/imask.min.js';
               $ruta5=plugin_dir_url(__FILE__) .'epayco-theme/vendor/jquery/jquery.min.js';
               $ruta6=plugin_dir_url(__FILE__) .'epayco-theme/vendor/jquery-validation/dist/jquery.validate.min.js';
               $ruta7=plugin_dir_url(__FILE__) .'epayco-theme/vendor/jquery-validation/dist/additional-methods.min.js';
               $ruta8=plugin_dir_url(__FILE__) .'epayco-theme/vendor/jquery-steps/jquery.steps.min.js';
               $ruta9=plugin_dir_url(__FILE__) .'epayco-theme/js/main.js';
               $ruta81=plugin_dir_url(__FILE__) .'epayco-theme/assets/js/validate.epayco.min.js';

               $tokenurl=plugin_dir_url(__FILE__) .'token.php';
              if($this->epayco_lang_sub=='es'){
                 $tadle= ' 
                 <label for="room_type" class="radio-label">Mi pedido</label>
                 <table class="table table-condensed">
                                       <tr><td>Descripcion</td>
                                       <td>'.$nameproductd.'</td>               
                                       </tr>
                                       <tr><td>Base iva </td>
                                           <td>'.$base_tax.' $</td> 
                                        </tr>
                                        <tr><td>iva </td>
                                          <td>'.$tax.'</td> 
                                        </tr>
                                        <tr><td>Precio</td>
                                          <td>'.$amountPayment.'</td> 
                                        </tr>
                                         <tr><td>Moneda</td>
                                          <td>'.$currency.'</td> 
                                        </tr>
                                      </table></div> ' ;
                                       $tipoDoc=  '<label for="country">Tipo de documento</label>';
                                       $table2=' <label for="email">Numero de documento</label>
                             <input type="text"  id="num_doc"  required />
                             <label for="email">Nombre</label>
                             <input type="email"  id="name"  data-epayco="card[name]" />
                             <label for="email">Direccion</label>
                             <input type="email"  id="adress"  value="'.$address_billing.'" />
                             <label for="email">Celular</label>
                             <input type="email"  id="Cellphone" value="'.$phone_billing.'" />
                             <label for="email">Email</label>
                            <input type="email"  id="email" data-epayco="card[email]" value="'.$email_billing.'" />';
                            $alert='<div id="snoAlertBox" class="alert alert-danger" data-alert="alert" 
                            style="
                            position:absolute;
                            z-index:1400;
                            top:2%;
                            right:4%;
                            margin:0px auto;
                            text-align:center;
                            display:none;
                            "> el numero de documento es requerido!</div>';
                            $alert2='<div id="snoAlertBox2" class="alert alert-danger" data-alert="alert" 
                            style="
                            position:absolute;
                            z-index:1400;
                            top:2%;
                            right:4%;
                            margin:0px auto;
                            text-align:center;
                            display:none;
                            ">el Nombre es requerido!</div>';
                             $alert3='<div id="snoAlertBox3" class="alert alert-danger" data-alert="alert" 
                            style="
                            position:absolute;
                            z-index:1400;
                            top:2%;
                            right:4%;
                            margin:0px auto;
                            text-align:center;
                            display:none;
                            ">El numero de la tarejta  es requerido!</div>';
                            $alert4='<div id="snoAlertBox4" class="alert alert-danger" data-alert="alert" 
                            style="
                            position:absolute;
                            z-index:1400;
                            top:2%;
                            right:4%;
                            margin:0px auto;
                            text-align:center;
                            display:none;
                            ">la fecha expiración de la tarjeta es requerida!</div>';
                            $alert5='<div id="snoAlertBox5" class="alert alert-danger" data-alert="alert" 
                            style="
                            position:absolute;
                            z-index:1400;
                            top:2%;
                            right:4%;
                            margin:0px auto;
                            text-align:center;
                            display:none;
                            ">CVC es requerido!</div>';

                                 

              }else{
            $tadle = ' <label for="room_type" class="radio-label">my order</label>
            <table class="table table-condensed">
                                       <tr><td>Description</td>
                                       <td>'.$nameproductd.'</td>               
                                       </tr>
                                       <tr><td>Base tax </td>
                                           <td>'.$base_tax.' $</td> 
                                        </tr>
                                        <tr><td>Tax </td>
                                          <td>'.$tax.'</td> 
                                        </tr>
                                        <tr><td>Amount </td>
                                          <td>'.$amountPayment.'</td> 
                                        </tr>
                                         <tr><td>Currency</td>
                                          <td>'.$currency.'</td> 
                                        </tr>
                                      </table> </div>';
                                      $tipoDoc=  '<label for="country">Document type</label>';
                                         $table2=' <label for="email">Document number</label>
                             <input type="text"  id="num_doc"  required />
                             <label for="email">Name</label>
                             <input type="email"  id="name"  data-epayco="card[name]" />
                             <label for="email">Address</label>
                             <input type="email"  id="adress"  value="'.$address_billing.'" />
                             <label for="email">Cellphone</label>
                             <input type="email"  id="Cellphone" value="'.$phone_billing.'" />
                             <label for="email">Email</label>
                            <input type="email"  id="email" data-epayco="card[email]" value="'.$email_billing.'" />';
                             $alert='<div id="snoAlertBox" class="alert alert-danger" data-alert="alert" 
                                style="
                                position:absolute;
                                z-index:1400;
                                top:2%;
                                right:4%;
                                margin:0px auto;
                                text-align:center;
                                display:none;
                                ">document number is required!</div>';
                  $alert2='<div id="snoAlertBox2" class="alert alert-danger" data-alert="alert" 
                                style="
                                position:absolute;
                                z-index:1400;
                                top:2%;
                                right:4%;
                                margin:0px auto;
                                text-align:center;
                                display:none;
                                ">Name is required!</div>';
                                $alert3='<div id="snoAlertBox3" class="alert alert-danger" data-alert="alert" 
                                style="
                                position:absolute;
                                z-index:1400;
                                top:2%;
                                right:4%;
                                margin:0px auto;
                                text-align:center;
                                display:none;
                                ">Credit card number is required!</div>';
                                 $alert4='<div id="snoAlertBox4" class="alert alert-danger" data-alert="alert" 
                            style="
                            position:absolute;
                            z-index:1400;
                            top:2%;
                            right:4%;
                            margin:0px auto;
                            text-align:center;
                            display:none;
                            ">the expiration date of the card is required!</div>';
                             $alert5='<div id="snoAlertBox5" class="alert alert-danger" data-alert="alert" 
                            style="
                            position:absolute;
                            z-index:1400;
                            top:2%;
                            right:4%;
                            margin:0px auto;
                            text-align:center;
                            display:none;
                            ">CVC is required!</div>';
                
              }
     
     
                echo('
                    <style>
                    </style>
                    ');
                echo sprintf(' 
                    <!DOCTYPE html>

<html lang="en">



<head>
  <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="author" content="colorlib.com">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <!-- Main css -->
     <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

     <link href="%s"rel="stylesheet">
     <link href="%s" rel="stylesheet">
     <link href="%s"rel="stylesheet">
 <script src="https://kit.fontawesome.com/da9e2bc31a.js"></script>
</head>


<body class="checkout">   
    
    <div class="main-checkout">

        <div class="container">
               <div class="wizard-header">               
                    <nav class="navbar navbar-default" role="navigation">
                        <div class="container">
                            <div class="col-sm-12">
                                <div class="navbar-header">
                                </div>
                                <div class="pull-right" style="position:center;">
                                    <p class="pull-right selloheader hidden-xs">  
                                          Pago seguro by
                                    <img class="img-logo"src="https://369969691f476073508a-60bf0867add971908d4f26a64519c2aa.ssl.cf5.rackcdn.com/logos/logo_epayco_200px.png" alt width="100%" height="20%">
                                    </p>
                               </div>
                           </div>
                        </div>
                    </nav>
               </div>
        </div>

          <div id="mostrardatos21" style="text-align:center;"></div>
          <div id="mostrardatos2"></div>



                <form method="POST" id="signup-form" class="signup-form" enctype="multipart/form-data">
                <h3>
                    <i class="fas fa-shopping-bag"></i>
                </h3>
                <fieldset>
                    <div class="form-row">
                        <div class="form-group">
                          
                               '.$tadle.'  
                        
                    </div>
                   
                </fieldset>


                <h3>
                   <i class="fas fa-user-circle"></i>
                </h3>
                <fieldset>
                    <div class="form-radio">
                        <div class="form-group"> '.$tipoDoc.'           
                        
                         <br><br><br><br>
      <select class="browser-default custom-select" style="
    width: 332px;
    height: 41px;
    text-align: center;
    font-size: larger;
    box-shadow: 0 0 15px #7a7a7a, 0 0 1px 1px rgba(0, 0, 0, 0.1);
    position: absolute;
    top: 22px;
    left: 20px;
    z-index: 999;
    display: block;
    width: 82%;
" name="doctype">
          <option value="CC" >CC</option>
                                    <option value="CE" >CE</option>
                                    <option value="PPN">PPN</option>
                                    <option value="SSN">SSN</option>
                                    <option value="LIC">LIC</option>
                                    <option value="NIT">NIT</option>
                                    <option value="DNI">DNI</option>
</select>
                              
                            '.$table2.'
                        </div>
                    </div>
                </fieldset>

                <h3>
                  <i class="fas fa-credit-card"></i>
                </h3>
                <fieldset> 
 <div id="mostrardatos21"></div>

       <div id="mostrardatos2"></div>
         '.$alert.$alert2.$alert3.$alert4.'
        <div id="mostrardatos3" hidden="true">'.$this->epayco_publickey_sub.'</div>
      <div class="container-card preload" id="sisas">
        <div class="creditcard">
            <div class="front">
                <div id="ccsingle"></div>
                <svg version="1.1" id="cardfront" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                    x="0px" y="0px" viewBox="0 0 750 471" style="enable-background:new 0 0 750 471;" xml:space="preserve">
                    <g id="Front">
                        <g id="CardBackground">
                            <g id="Page-1_1_">
                                <g id="amex_1_">
                                    <path id="Rectangle-1_1_" class="lightcolor grey" d="M40,0h670c22.1,0,40,17.9,40,40v391c0,22.1-17.9,40-40,40H40c-22.1,0-40-17.9-40-40V40
                            C0,17.9,17.9,0,40,0z" />
                                </g>
                            </g>
                            <path class="darkcolor greydark" d="M750,431V193.2c-217.6-57.5-556.4-13.5-750,24.9V431c0,22.1,17.9,40,40,40h670C732.1,471,750,453.1,750,431z" />
                        </g>
                        <text transform="matrix(1 0 0 1 60.106 295.0121)" id="svgnumber" class="st2 st3 st4">0123 4567 8910 1112</text>
                        <text transform="matrix(1 0 0 1 54.1064 428.1723)" id="svgname" class="st2 st5 st6">JOHN DOE</text>
                        <text transform="matrix(1 0 0 1 54.1074 389.8793)" class="st7 st5 st8">cardholder name</text>
                        <text transform="matrix(1 0 0 1 479.7754 388.8793)" class="st7 st5 st8">expiration</text>
                        <text transform="matrix(1 0 0 1 65.1054 241.5)" class="st7 st5 st8">card number</text>
                        <g>
                            <text transform="matrix(1 0 0 1 574.4219 433.8095)" id="svgexpire" class="st2 st5 st9">01/23</text>
                            <text transform="matrix(1 0 0 1 479.3848 417.0097)" class="st2 st10 st11">VALID</text>
                            <text transform="matrix(1 0 0 1 479.3848 435.6762)" class="st2 st10 st11">THRU</text>
                            <polygon class="st2" points="554.5,421 540.4,414.2 540.4,427.9    " />
                        </g>
                        <g id="cchip">
                            <g>
                                <path class="st2" d="M168.1,143.6H82.9c-10.2,0-18.5-8.3-18.5-18.5V74.9c0-10.2,8.3-18.5,18.5-18.5h85.3
                        c10.2,0,18.5,8.3,18.5,18.5v50.2C186.6,135.3,178.3,143.6,168.1,143.6z" />
                            </g>
                            <g>
                                <g>
                                    <rect x="82" y="70" class="st12" width="1.5" height="60" />
                                </g>
                                <g>
                                    <rect x="167.4" y="70" class="st12" width="1.5" height="60" />
                                </g>
                                <g>
                                    <path class="st12" d="M125.5,130.8c-10.2,0-18.5-8.3-18.5-18.5c0-4.6,1.7-8.9,4.7-12.3c-3-3.4-4.7-7.7-4.7-12.3
                            c0-10.2,8.3-18.5,18.5-18.5s18.5,8.3,18.5,18.5c0,4.6-1.7,8.9-4.7,12.3c3,3.4,4.7,7.7,4.7,12.3
                            C143.9,122.5,135.7,130.8,125.5,130.8z M125.5,70.8c-9.3,0-16.9,7.6-16.9,16.9c0,4.4,1.7,8.6,4.8,11.8l0.5,0.5l-0.5,0.5
                            c-3.1,3.2-4.8,7.4-4.8,11.8c0,9.3,7.6,16.9,16.9,16.9s16.9-7.6,16.9-16.9c0-4.4-1.7-8.6-4.8-11.8l-0.5-0.5l0.5-0.5
                            c3.1-3.2,4.8-7.4,4.8-11.8C142.4,78.4,134.8,70.8,125.5,70.8z" />
                                </g>
                                <g>
                                    <rect x="82.8" y="82.1" class="st12" width="25.8" height="1.5" />
                                </g>
                                <g>
                                    <rect x="82.8" y="117.9" class="st12" width="26.1" height="1.5" />
                                </g>
                                <g>
                                    <rect x="142.4" y="82.1" class="st12" width="25.8" height="1.5" />
                                </g>
                                <g>
                                    <rect x="142" y="117.9" class="st12" width="26.2" height="1.5" />
                                </g>
                            </g>
                        </g>
                    </g>
                    <g id="Back">
                    </g>
                </svg>
            </div>
            <div class="back">
                <svg version="1.1" id="cardback" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                    x="0px" y="0px" viewBox="0 0 750 471" style="enable-background:new 0 0 750 471;" xml:space="preserve">
                    <g id="Front">
                        <line class="st0" x1="35.3" y1="10.4" x2="36.7" y2="11" />
                    </g>
                    <g id="Back">
                        <g id="Page-1_2_">
                            <g id="amex_2_">
                                <path id="Rectangle-1_2_" class="darkcolor greydark" d="M40,0h670c22.1,0,40,17.9,40,40v391c0,22.1-17.9,40-40,40H40c-22.1,0-40-17.9-40-40V40
                        C0,17.9,17.9,0,40,0z" />
                            </g>
                        </g>
                        <rect y="61.6" class="st2" width="750" height="78" />
                        <g>
                            <path class="st3" d="M701.1,249.1H48.9c-3.3,0-6-2.7-6-6v-52.5c0-3.3,2.7-6,6-6h652.1c3.3,0,6,2.7,6,6v52.5
                    C707.1,246.4,704.4,249.1,701.1,249.1z" />
                            <rect x="42.9" y="198.6" class="st4" width="664.1" height="10.5" />
                            <rect x="42.9" y="224.5" class="st4" width="664.1" height="10.5" />
                            <path class="st5" d="M701.1,184.6H618h-8h-10v64.5h10h8h83.1c3.3,0,6-2.7,6-6v-52.5C707.1,187.3,704.4,184.6,701.1,184.6z" />
                        </g>
                        <text transform="matrix(1 0 0 1 621.999 227.2734)" id="svgsecurity" class="st6 st7">985</text>
                        <g class="st8">
                            <text transform="matrix(1 0 0 1 518.083 280.0879)" class="st9 st6 st10">security code</text>
                        </g>
                        <rect x="58.1" y="378.6" class="st11" width="375.5" height="13.5" />
                        <rect x="58.1" y="405.6" class="st11" width="421.7" height="13.5" />
                        <text transform="matrix(1 0 0 1 59.5073 228.6099)" id="svgnameback" class="st12 st13">John Doe</text>
                    </g>
                </svg>
            </div>
        </div>
    </div>

  <div  id="#mostrardatos21"></div>
        <div class="form-container-card" id="customer-sisas">
        <form  method="POST" id="customer-form">
      <div class="field-container-card">       
   <div class="form-group">
      <div class="row justify-content-center mt-8">
        <div class="col-12 col-md-12">
          <div class="input-group flex-nowrap" hidden="true">
            <div class="input-group-prepend">
              <span class="input-group-text" id="addon-wrapping"><i class="fas fa-user-circle"></i></span>
            </div>
            <input id="name2" maxlength="20" type="text" value="'.$name_billing.'">
            <svg id="ccicon" class="ccicon" width="750" height="471" viewBox="0 0 750 471" version="1.1" xmlns="http://www.w3.org/2000/svg"
                xmlns:xlink="http://www.w3.org/1999/xlink">
            </svg>
          </div>
        </div>
      </div>
    </div>
 </div>

<div class="field-container-card">       
   <div class="form-group">
      <div class="row justify-content-center mt-8">
        <div class="col-12 col-md-12">
          <div class="input-group flex-nowrap" hidden="true">
            <div class="input-group-prepend">
              <span class="input-group-text" id="addon-wrapping"><i class="fas fa-envelope-square"></i></span>
            </div>
           <input id="email2"  type="text"  required value="'.$email_billing.'">
            <svg id="ccicon" class="ccicon" width="750" height="471" viewBox="0 0 750 471" version="1.1" xmlns="http://www.w3.org/2000/svg"
                xmlns:xlink="http://www.w3.org/1999/xlink">
            </svg>
          </div>
        </div>
      </div>
    </div>
 </div>

<div class="field-container-card">       
   <div class="form-group">
      <div class="row justify-content-center mt-8">
        <div class="col-12 col-md-12">
          <div class="input-group flex-nowrap">
            <div class="input-group-prepend">
              <span class="input-group-text" id="addon-wrapping"><i class="fas fa-credit-card"></i></span>
            </div>
            <input id="cardnumber" type="text"  inputmode="numeric" data-epayco="card[number]" required onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxlength="20" minlength="13"  placeholder="Card number">
            <svg id="ccicon" class="ccicon" width="750" height="471" viewBox="0 0 750 471" version="1.1" xmlns="http://www.w3.org/2000/svg"
                xmlns:xlink="http://www.w3.org/1999/xlink">
            </svg>
          </div>
        </div>
      </div>
    </div>
 </div>

        <div class="field-container-card">
            <label for="expirationdate"  hidden="true">Expiration (mm/yy)</label>
            <input id="expirationdate" type="text" pattern="[0-9]*" inputmode="numeric"  hidden="true">
        </div>

        <div class="field-container-card">       
   <div class="form-group">
      <div class="row justify-content-center mt-8">
        <div class="col-12 col-md-12">
          <div class="input-group flex-nowrap">
            <div class="input-group-prepend">
              <span class="input-group-text" id="addon-wrapping"><i class="fas fa-calendar-alt"></i></span>
            </div>
          <input type="text" data-epayco="card[exp_month]" id="exp_month" onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxlength="2" placeholder="MM"  required>
        <input type="text" data-epayco="card[exp_year]" id="exp_year" onkeypress="return event.charCode >= 48 && event.charCode <= 57" maxlength="4" minlength="4"required placeholder="YY">
            <svg id="ccicon" class="ccicon" width="750" height="471" viewBox="0 0 750 471" version="1.1" xmlns="http://www.w3.org/2000/svg"
                xmlns:xlink="http://www.w3.org/1999/xlink">
            </svg>
          </div>
        </div>
      </div>
    </div>
 </div>

        <div class="field-container-card">       
   <div class="form-group">
      <div class="row justify-content-center mt-8">
        <div class="col-12 col-md-12">
          <div class="input-group flex-nowrap">
            <div class="input-group-prepend">
              <span class="input-group-text" id="addon-wrapping"><i class="fas fa-unlock-alt"></i></span>
            </div>
          <input id="securitycode" type="password" pattern="[0-9]*" inputmode="numeric" data-epayco="card[cvc]"  placeholder="CVC">
            <svg id="ccicon" class="ccicon" width="750" height="471" viewBox="0 0 750 471" version="1.1" xmlns="http://www.w3.org/2000/svg"
                xmlns:xlink="http://www.w3.org/1999/xlink">
            </svg>
          </div>
        </div>
      </div>
    </div>
 </div>
         <button type="submit" hidden="true">¡Pagar ahora!</button>
         </form>
    </div>
                </fieldset>

            </form>
<div id="tokenurl" hidden="true">'.$tokenurl.'</div>
<div id="myarray" hidden="true">'.$datasuscription.'</div>
<div id="p_c" hidden="true">'.$this->epayco_secretkey_sub.'</div>
<div id="lang" hidden="true">'.$this->epayco_lang_sub.'</div>
<div id="test" hidden="true">'.$testMode.'</div>
<div id="response" hidden="true">'.$redirect_url.'</div>
<div id="confirmacion" hidden="true">'.$confirm_url.'</div>
<div id="city" hidden="true">'.$order_billing_city.'</div>
<div id="ip" hidden="true"></div>
<div id="descripcion_p" hidden="true">'.$nameproductd.'</div>
<div id="currency" hidden="true">'.$currency.'</div>
<div id="amount" hidden="true">'.$amountPayment.'</div>
    </div>         
</body>

<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="'.$ruta81.'"></script>
 <script src="'.$ruta3.'"></script>
  <script src="'.$ruta4.'"></script>
  <script src="'.$ruta5.'"></script>
  <script src="'.$ruta6.'"></script>
  <script src="'.$ruta7.'"></script>
<script src="'.$ruta8.'"></script>
   <script src="'.$ruta9.'"></script>
</html>
                ',$ruta0,$ruta1,$ruta2,$address_billing,$phone_billing,$email_billing,
                $name_billing,$ruta3,$ruta4,$ruta5,$ruta6,$ruta7,$ruta8,$ruta9);
                    $js = "if(jQuery('button.epayco-button-render').length)    

                {
                jQuery('button.epayco-button-render').css('margin','auto');
                jQuery('button.epayco-button-render').css('display','block');
                }
                ";
                if (version_compare(WOOCOMMERCE_VERSION, '2.1', '>=')){
                    wc_enqueue_js($js);

                }else{
                    $woocommerce->add_inline_js($js);
                }
            }


            public function block($message)
            {
                return 'jQuery("body").block({
                        message: "' . esc_js($message) . '",
                        baseZ: 99999,
                        overlayCSS:
                        {
                            background: "#000",
                            opacity: "0.6",
                        },
                        css: {
                            padding:        "20px",
                            zindex:         "9999999",
                            textAlign:      "center",
                            color:          "#555",
                            border:         "1px solid #aaa",
                            backgroundColor:"#fff",
                            cursor:         "wait",
                            lineHeight:     "24px",
                        }
                    });';
            }

            public function agafa_dades($url) {
                if (function_exists('curl_init')) {
                    $ch = curl_init();
                    $timeout = 5;
                    $user_agent='Mozilla/5.0 (Windows NT 6.1; rv:8.0) Gecko/20100101 Firefox/8.0';
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                    curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
                    curl_setopt($ch,CURLOPT_TIMEOUT,$timeout);
                    curl_setopt($ch,CURLOPT_MAXREDIRS,10);
                    $data = curl_exec($ch);
                    curl_close($ch);
                    return $data;
                }else{
                    $data =  @file_get_contents($url);
                    return $data;
                }
            }

            public function goter(){
                $context = stream_context_create(array(
                    'http' => array(
                        'method' => 'POST',
                        'header' => 'Content-Type: application/x-www-form-urlencoded',
                        'protocol_version' => 1.1,
                        'timeout' => 10,
                        'ignore_errors' => true
                    )
                ));
            }

            function check_ePayco_response_sub(){
                @ob_clean();
                if ( ! empty( $_REQUEST ) ) {
                    header( 'HTTP/1.1 200 OK' );
                    do_action( "ePayco_init_sub", $_REQUEST );
                } else {
                    wp_die( __("ePayco Request Failure", 'epayco-woocommerce') );
                }
            }

            /**
             * @param $validationData
             */

            function ePayco_successful_request_sub($validationData)
            {
                    global $woocommerce;
                    $signature="";
                    if(isset($_REQUEST['x_signature'])){
                        $explode=explode('?',$_GET['order_id']);
                        $order_id=$explode[0];
                        $ref_payco=$_REQUEST['x_ref_payco'];

                    }else{

                        $explode=explode('?',$_GET['order_id']);
                        $explode2=explode('?',$_GET['ref_payco']);
                        $order_id=$explode[0];
                        $strref_payco=explode("=",$explode[1]);
                        $ref_payco=$strref_payco[1];    
                     if ( !$ref_payco) {
                        $ref_payco=$explode2[0];
                     }
                            //Consultamos los datos
                            $message = __('Esperando respuesta por parte del servidor.','payco-woocommerce');
                            $url= 'https://secure.payco.co/pasarela/estadotransaccion?id_transaccion='.$ref_payco;
                            $responseData = $this->agafa_dades($url,false,$this->goter());
                            $jsonData = @json_decode($responseData, true);

                            $validationData = $jsonData['data'];
                           $ref_payco = $validationData['x_ref_payco'];
                }
                    //Validamos la firma
                    if ($order_id!="" && $ref_payco!="") {
                        $order = new WC_Order($order_id);
                        $signature = hash('sha256',
                            trim($this->epayco_customerid_sub).'^'
                            .trim($this->epayco_p_key_sub).'^'
                            .$validationData['x_ref_payco'].'^'
                            .$validationData['x_transaction_id'].'^'
                            .$validationData['x_amount'].'^'
                            .$validationData['x_currency_code']
                        );
                    }
                        $order = new WC_Order($order_id);

                    var_dump($order_id,$ref_payco,$validationData['x_signature']);

                    $message = '';
                    $messageClass = '';
                    $current_state = $order->get_status();

                    if($signature == trim($validationData['x_signature']))
                    {

                switch ((int)$validationData['x_cod_transaction_state']) {
                        case 1:{
                            $newOrderSuscribe=intval($order_id);
                            $subscriptions_ids = wcs_get_subscriptions_for_order( $order_id );
                        foreach( $subscriptions_ids as $subscription_id => $subscription_obj ){

                        if($subscription_obj->order->id == $order_id) break; // Stop the loop
                        }
                         $susCription_id=$subscription_obj->id;

                         if (!$subscription_obj->id) {
                        $susCription_id=$newOrderSuscribe;
                    }
                        
                        $subscription = new WC_Subscription($susCription_id);
                        //$x_id_factura = $validationData['x_id_factura'];
                        $x_id_factura = $validationData['x_id_invoice'];
                        $x_id_factura = explode('-', $x_id_factura);
                        $subscription_id = $x_id_factura[0];

                                //Busca si ya se descontó el stock


                        if (!EpaycoOrders::ifStockDiscount($order_id)) {
                            if (EpaycoOrders::updateStockDiscount($order_id,1)) {
                                $this->restore_order_stock($order_id,'decrease');
                                    }
                                }

                                $message = 'Pago exitoso';
                                $messageClass = 'woocommerce-message';
                                $order->payment_complete($validationData['x_ref_payco']);
                                $order->update_status('Processing');
                                $order->add_order_note('Pago exitoso');
                            
                                $subscription->payment_complete();
                                $note  = sprintf(__('Successful subscription (subscription ID: %s), reference (%s)', 'subscription-epayco'),
                                 $subscription_id, $validationData['x_ref_payco']);
                                $subscription->update_status( 'active', $note );
                                $subscription->add_order_note($note);
                                update_post_meta($subscription->get_id(), 'subscription_id', $subscription_id);

                            }break;

                            case 2: {
                            $newOrderSuscribe=intval($order_id);
                            $subscriptions_ids = wcs_get_subscriptions_for_order( $order_id );
                            foreach( $subscriptions_ids as $subscription_id => $subscription_obj ){
 
                             if($subscription_obj->order->id == $order_id) break; // Stop the loop
                                }
                            $susCription_id=$subscription_obj->id;

                            if (!$subscription_obj->id) {
                             $susCription_id=$newOrderSuscribe;
                                }
                        
                        $subscription = new WC_Subscription($susCription_id);
                        $subscription->payment_failed();
                       // $subscription->update_status( 'cancelled' );
                       $subscription->update_status( 'expired' );

                            }break;

                            case 3:{
                                $message = 'Pago pendiente de aprobación';
                                $messageClass = 'woocommerce-info';
                            }break;

                            case 4:{
                            $newOrderSuscribe=intval($order_id);
                            $subscriptions_ids = wcs_get_subscriptions_for_order( $order_id );
                            foreach( $subscriptions_ids as $subscription_id => $subscription_obj ){
                             if($subscription_obj->order->id == $order_id) break; // Stop the loop
                                }
                            $susCription_id=$subscription_obj->id;
                            if (!$subscription_obj->id) {
                             $susCription_id=$newOrderSuscribe;
                                }
                        
                        $subscription = new WC_Subscription($susCription_id);
                        $subscription->payment_failed();
                        $subscription->update_status( 'cancelled', 'Your subscription has been cancelled.' );
                                $message = 'Pago fallido';
                                $messageClass = 'woocommerce-error';
                                $order->update_status('epayco-failed');
                                $order->add_order_note('Pago fallido');
                            }break;

                            default:{
                                $message = 'Pago fallido';
                                $messageClass = 'woocommerce-error';
                                $order->update_status('epayco-failed');
                                $order->add_order_note($message);

                            }break;
                        }

                    //validar si la transaccion esta pendiente y pasa a rechazada y ya habia descontado el stock

                    if($current_state == 'epayco-on-hold' || $current_state == 'on-hold'&& ((int)$validationData['x_cod_transaction_state'] == 2 || (int)$validationData['x_cod_transaction_state'] == 4) && EpaycoOrders::ifStockDiscount($order_id)){

                        //si no se restauro el stock restaurarlo inmediatamente
                         $this->restore_order_stock($order_id);

                    };
                    }else {
                      //no incide la firma
                        $message = 'Firma no valida';
                        $messageClass = 'error';
                        $order->update_status('failed');
                        $order->add_order_note('Failed');
                    }

                    if (isset($_REQUEST['confirmation'])) {
                        $redirect_url = get_permalink($this->get_option('epayco_url_confirmation_sub'));
                        if ($this->get_option('epayco_url_confirmation_sub' ) == 0) {
                           echo "ok";
                            die();
                        }
                    }else{

                        if ($this->get_option('epayco_url_response_sub' ) == 0) {
                            $redirect_url = $order->get_checkout_order_received_url();
                        }else{
                            $woocommerce->cart->empty_cart();
                            $redirect_url = get_permalink($this->get_option('epayco_url_response_sub'));
                        }
                    }

                    $arguments=array();
                    foreach ($validationData as $key => $value) {
                        $arguments[$key]=$value;
                    }

                    unset($arguments["wc-api"]);
                    $arguments['msg']=urlencode($message);
                    $arguments['type']=$messageClass;
                    $redirect_url = add_query_arg($arguments , $redirect_url );
                    wp_redirect($redirect_url);
                    die();
            }

            /**
             * @param $order_id
             */

            public function restore_order_stock($order_id,$operation = 'increase')
            {

                  $order = wc_get_order($order_id);
                if (!get_option('woocommerce_manage_stock') == 'yes' && !sizeof($order->get_items()) > 0) {
                    return;
                }

                foreach ($order->get_items() as $item) {
                    // Get an instance of corresponding the WC_Product object
                    $product = $item->get_product();
                    $qty = $item->get_quantity(); // Get the item quantity
                    wc_update_product_stock($product, $qty, $operation);
                }
            }

            public function string_sanitize($string, $force_lowercase = true, $anal = false) {

                $strip = array("~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "=", "+", "[", "{", "]","}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;","â€”", "â€“", ",", "<", ".", ">", "/", "?");
                $clean = trim(str_replace($strip, "", strip_tags($string)));
                $clean = preg_replace('/\s+/', "_", $clean);
                $clean = ($anal) ? preg_replace("/[^a-zA-Z0-9]/", "", $clean) : $clean ;
                return $clean;
            }

            public function getTaxesOrder($order){
                $taxes=($order->get_taxes());
                $tax=0;
                foreach($taxes as $tax){
                    $itemtax=$tax['item_meta']['tax_amount'][0];
                }
                return $itemtax;
            }
        }

        /**
         * @param $methods
         * @return array
         */

        function woocommerce_epayco_add_gateway_sub($methods)
        {
            $methods[] = 'WC_ePayco_sub';
            return $methods;
        }



        add_filter('woocommerce_payment_gateways', 'woocommerce_epayco_add_gateway_sub');

        function epayco_woocommerce_sub_addon_settings_link_sub( $links ) {
            array_push( $links, '<a href="admin.php?page=wc-settings&tab=checkout&section=epayco_sub">' . __( 'Configuración' ) . '</a>' );
            return $links;
        }

        add_filter( "plugin_action_links_".plugin_basename( __FILE__ ),'epayco_woocommerce_sub_addon_settings_link_sub' );
    }

    //Actualización de versión
    global $epayco_db_version_sub;
    $epayco_db_version_sub = '1.0';
    function epayco_update_db_check_sub()
    {
        global $epayco_db_version_sub;
        $installed_ver = get_option('epayco_db_version_sub');
        if ($installed_ver == null || $installed_ver != $epayco_db_version_sub) {
            EpaycoOrders::setup();
            update_option('epayco_db_version_sub', $epayco_db_version_sub);
        }

    }

    add_action('plugins_loaded', 'epayco_update_db_check_sub');

    function register_epayco_order_status_sud() {
        register_post_status( 'wc-epayco-failed', array(
            'label'                     => 'ePayco Pago Fallido',
            'public'                    => true,
            'show_in_admin_status_list' => true,
            'show_in_admin_all_list'    => true,
            'exclude_from_search'       => false,
            'label_count'               => _n_noop( 'ePayco Pago Fallido <span class="count">(%s)</span>', 'ePayco Pago Fallido <span class="count">(%s)</span>' )
        ));

        register_post_status( 'wc-epayco-canceled', array(
            'label'                     => 'ePayco Pago Cancelado',
            'public'                    => true,
            'show_in_admin_status_list' => true,
            'show_in_admin_all_list'    => true,
            'exclude_from_search'       => false,
            'label_count'               => _n_noop( 'ePayco Pago Cancelado <span class="count">(%s)</span>', 'ePayco Pago Cancelado <span class="count">(%s)</span>' )
        ));

        register_post_status( 'wc-epayco-on-hold', array(
            'label'                     => 'ePayco Pago Pendiente',
            'public'                    => true,
            'show_in_admin_status_list' => true,
            'show_in_admin_all_list'    => true,
            'exclude_from_search'       => false,
            'label_count'               => _n_noop( 'ePayco Pago Pendiente <span class="count">(%s)</span>', 'ePayco Pago Pendiente <span class="count">(%s)</span>' )
        ));

        register_post_status( 'wc-epayco-processing', array(
            'label'                     => 'ePayco Procesando Pago',
            'public'                    => true,
            'show_in_admin_status_list' => true,
            'show_in_admin_all_list'    => true,
            'exclude_from_search'       => false,
            'label_count'               => _n_noop( 'ePayco Procesando Pago <span class="count">(%s)</span>', 'ePayco Procesando Pago <span class="count">(%s)</span>' )
        ));

        register_post_status( 'wc-epayco-completed', array(
            'label'                     => 'ePayco Pago Completado',
            'public'                    => true,
            'show_in_admin_status_list' => true,
            'show_in_admin_all_list'    => true,
            'exclude_from_search'       => false,
            'label_count'               => _n_noop( 'ePayco Pago Completado <span class="count">(%s)</span>', 'ePayco Pago Completado <span class="count">(%s)</span>' )

        ));

    }



    add_action( 'plugins_loaded', 'register_epayco_order_status_sud' );
    function add_epayco_to_order_statuses_sub( $order_statuses ) {
        $new_order_statuses = array();
        foreach ( $order_statuses as $key => $status ) {
            $new_order_statuses[ $key ] = $status;
            if ( 'wc-cancelled' === $key ) {
              $new_order_statuses['wc-epayco-cancelled'] = 'ePayco Pago Cancelado';
            }

            if ( 'wc-failed' === $key ) {
                $new_order_statuses['wc-epayco-failed'] = 'ePayco Pago Fallido';
            }

            if ( 'wc-on-hold' === $key ) {
                $new_order_statuses['wc-epayco-on-hold'] = 'ePayco Pago Pendiente';
            }

            if ( 'wc-processing' === $key ) {
                $new_order_statuses['wc-epayco-processing'] = 'ePayco Procesando Pago';
            }

            if ( 'wc-completed' === $key ) {
                $new_order_statuses['wc-epayco-completed'] = 'ePayco Pago Completado';
            }
        }
        return $new_order_statuses;

    }


    add_filter( 'wc_order_statuses', 'add_epayco_to_order_statuses_sub' );

    add_action('admin_head', 'styling_admin_order_list_sub' );

    function styling_admin_order_list_sub() {
        global $pagenow, $post;

        if( $pagenow != 'edit.php') return; // Exit
        if( get_post_type($post->ID) != 'shop_order' ) return; // Exit

        // HERE we set your custom status
        $order_status_failed = 'epayco-failed';
        $order_status_on_hold = 'epayco-on-hold';
        $order_status_processing = 'epayco-processing';
        $order_status_completed = 'epayco-completed';
       ?>
       <style>
            .order-status.status-<?php echo sanitize_title( $order_status_failed); ?> {
                background: #eba3a3;
                color: #761919;
            }
            .order-status.status-<?php echo sanitize_title( $order_status_on_hold); ?> {
                background: #f8dda7;
                color: #94660c;
            }
            .order-status.status-<?php echo sanitize_title( $order_status_processing ); ?> {
                background: #c8d7e1;
                color: #2e4453;
            }
            .order-status.status-<?php echo sanitize_title( $order_status_completed ); ?> {
                background: #d7f8a7;
                color: #0c942b;
            }
        </style>
        <?php
    }
}