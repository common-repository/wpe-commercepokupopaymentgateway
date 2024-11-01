<?php
/**
 * Plugin Name: WP eCommerce Pokupo Payment Gateway
 * Description: Pokupo payment API integration for WP eCommerce
 * Version:     1.0.0
 * Author:      Pokupo
 * Author URI:  https://pokupo.ru/
 *
 * @package ecommerce-payment-pokupo
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$nzshpcrt_gateways[$num]['name'] = 'Pokupo';
$nzshpcrt_gateways[$num]['internalname'] = 'Pokupo';
$nzshpcrt_gateways[$num]['image'] = plugin_dir_url( __FILE__ ) . 'pokupo.svg';
$nzshpcrt_gateways[$num]['function'] = 'wpplugin_wpec_pokupo_gateway';
$nzshpcrt_gateways[$num]['form'] = 'wpplugin_wpec_pokupo_form';
$nzshpcrt_gateways[$num]['submit_function'] = 'wpplugin_wpec_pokupo_submit';
$nzshpcrt_gateways[$num]['payment_type'] = "pokupo";
$nzshpcrt_gateways[$num]['display_name'] = __('Pokupo', 'wpsc');


//оформление комментариев в форме настроек платежного шлюза
function wpplugin_wpec_pokupo_form_hint($s)
{
    return '<small style="line-height:14px;display:block;padding:2px 0 6px;">' . $s . '</small>';
}

// Форма настроек платежного шлюза
function wpplugin_wpec_pokupo_form()
{
    // Get stored values.
    $shop_id = get_option('pokupo_shop_id');
    $notification_password = get_option('pokupo_notification_password');
    $merchant_language = get_option('pokupo_merchant_language');
    //$result_method = get_option('pokupo_result_method');
    $successurl = get_option('pokupo_success_url');
    if(!$successurl) $successurl = get_option('transact_url');
    $failurl = get_option('pokupo_fail_url');
    if(!$failurl) $failurl = get_option('shopping_cart_url');

    $language = strtolower(substr(get_locale(), 0, 2));
    $callbackurl = get_option('siteurl') . "/?pokupo_result=1";

    // Generate output.
    $output = '<tr><td colspan="2" style="text-align:center;"><br/><a href="https://pokupo.ru/" target="_new"><img src="'.plugin_dir_url( __FILE__ ) . 'pokupo.svg'.'"/></a></td></tr>';
    if ($language == 'ru') $output .= '<tr><td colspan="2"><strong>Настройки платёжного шлюза</strong></td></tr>';
    else $output .= '<tr><td colspan="2"><strong>Merchant configuration</strong></td></tr>';

    // Shop ID.
    if ($language == 'ru') $output .= '<tr><td><label for="pokupo_shop_id">ID магазина:</label></td>';
    else $output .= '<tr><td><label for="pokupo_shop_id">Shop ID:</label></td>';
    $output .= '<td><input name="pokupo_shop_id" id="pokupo_shop_id" type="text" value="' . $shop_id . '"/><br/>';
    if ($language == 'ru') $output .= wpplugin_wpec_pokupo_form_hint('ID магазина из поля `ID магазина для CMS` в настройках магазина в личном кабинете Pokupo');
    else $output .= wpplugin_wpec_pokupo_form_hint('ID from field `ID shop from CMS` in pokupo private section');
    $output .= '</td></tr>';

    // Notification Password
    if ($language == 'ru') $output .= '<tr><td><label for="pokupo_notification_password">Пароль уведомлений:</label></td>';
    else $output .= '<tr><td><label for="pokupo_notification_password">Notification Password:</label></td>';
    $output .= '<td><input name="pokupo_notification_password" id="pokupo_notification_password" type="text" value="' . $notification_password . '"/><br/>';
    if ($language == 'ru') $output .= wpplugin_wpec_pokupo_form_hint('Пароль уведомлений указан в настройках магазина в личном кабинете Pokupo');
    else $output .= wpplugin_wpec_pokupo_form_hint('Notification password is specified in pokupo private section');
    $output .= '</td></tr>';




    //Merchant Language
    if ($language == 'ru') $output .= '<tr><td><label for="pokupo_merchant_language">Язык мерчанта:</label></td>';
    else $output .= '<tr><td><label for="pokupo_merchant_language">Merchant Language:</label></td>';
    $output .= '<td><select name="pokupo_merchant_language" id="pokupo_merchant_language">';
    $output .= '<option value="ru"' . ($merchant_language == 'ru' ? ' selected="selected"' : '') . '>ru</option>';
    $output .= '<option value="en"' . ($merchant_language == 'en' ? ' selected="selected"' : '') . '>en</option>';
    $output .= '</select></td></tr>';

    // Notification URL
    if ($language == 'ru') $output .= '<tr><td>URL уведомлений:</td>';
    else $output .= '<tr><td>Notification URL</td>';
    $output .= '<td>' . $callbackurl . '</td></tr>';

    // Success URL
    $output .= '<tr><td><label for="pokupo_success_url">Success URL</label></td>';
    $output .= '<td><input name="pokupo_success_url" id="pokupo_success_url" type="text" value="' . $successurl . '"/><br/>';
    if ($language == 'ru') $output .= wpplugin_wpec_pokupo_form_hint('URL перенаправления при успешной оплате');
    else $output .= wpplugin_wpec_pokupo_form_hint('URL to redirect to after successful payment');
    $output .= '</td></tr>';

    // Fail URL
    $output .= '<tr><td><label for="pokupo_fail_url">Fail URL</label></td>';
    $output .= '<td><input name="pokupo_fail_url" id="pokupo_fail_url" type="text" value="' . $failurl . '"/><br/>';
    if ($language == 'ru') $output .= wpplugin_wpec_pokupo_form_hint('URL перенаправления при неуспешной оплате');
    else $output .= wpplugin_wpec_pokupo_form_hint('URL to redirect to after unsuccessful payment');
    $output .= '</td></tr>';


    return $output;
}

// Сохраняет данные формы Настроек платежного шлюза
function wpplugin_wpec_pokupo_submit()
{
    $shop_id=sanitize_key((int)$_POST['pokupo_shop_id']);
    $notify_pass=sanitize_text_field($_POST['pokupo_notification_password']);
    $success_url=esc_url($_POST['pokupo_success_url']);
    $fail_url=esc_url($_POST['pokupo_fail_url']);
    $merchant_lang=sanitize_key($_POST['pokupo_merchant_language']);

    if ($shop_id != null)
        update_option('pokupo_shop_id', $shop_id);

    if ($notify_pass != null)
        update_option('pokupo_notification_password', $notify_pass);

    if ($success_url != null)
        update_option('pokupo_success_url', $success_url);

    if ($fail_url != null)
        update_option('pokupo_fail_url', $fail_url);

    if ($merchant_lang != null)
        update_option('pokupo_merchant_language', $merchant_lang);

    return true;
}


//формирование формы для отправки заказа
function wpplugin_wpec_pokupo_gateway($seperator, $session_id=0)
{
    if(isset($session_id))
    {
        global $wpdb, $wpsc_cart;
        $shop_id = get_option('pokupo_shop_id');
        $success_url = get_option('pokupo_success_url');
        $fail_url = get_option('pokupo_fail_url');
        $merchant_language = get_option('pokupo_merchant_language');
        $amount = round($wpsc_cart->total_price, 2);
        $email = sanitize_email($_POST['collected_data']['9']);
        $language = strtolower(substr(get_locale(), 0, 2));
        $order_id = $wpdb->get_var("SELECT id FROM " . WPSC_TABLE_PURCHASE_LOGS . " WHERE sessionid = '".sanitize_key($session_id)."' LIMIT 1;");
        $wpdb->query("UPDATE " . WPSC_TABLE_PURCHASE_LOGS . " SET processed = '1', date = '" . time() . "' WHERE sessionid = " . sanitize_key($session_id) . " LIMIT 1");
        $amount = number_format($amount, 2, '.', '');
        $desc = 'Payment for shop '.$shop_id;
        $url = 'https://seller.pokupo.ru/api/'.$merchant_language.'/payment/merchant';

        // Generate the form output.
        $output = "<div style=\"display:none;\">
        <form id=\"pokupo_form\" name=\"pokupo_form\" action=\"$url\" method=\"get\">
        <input type=\"hidden\" name=\"LMI_PAYEE_PURSE\" value=\"$shop_id\"/>
        <input type=\"hidden\" name=\"LMI_PAYMENT_NO\" value=\"$order_id\"/>
        <input type=\"hidden\" name=\"LMI_PAYMENT_AMOUNT\" value=\"$amount\"/>
        <input type=\"hidden\" name=\"LMI_PAYMENT_DESC\" value=\"$desc\"/>
        <input type=\"hidden\" name=\"CLIENT_MAIL\" value=\"$email\"/>
        <input type=\"hidden\" name=\"LMI_SUCCESS_URL\" value=\"$success_url\"/>
        <input type=\"hidden\" name=\"LMI_FAIL_URL\" value=\"$fail_url\"/>
        <input type=\"hidden\" name=\"SESSION_ID\" value=\"$session_id\"/>";

        if ($language == 'ru') {
            $output .= "<input type=\"hidden\" name=\"pokupoPurpose\" value=\"Заказ №" . $order_id . "\"/>
            <input type=\"submit\" value=\"Оплатить\"/>";
        } else {
            $output .= "<input type=\"hidden\" name=\"pokupoPurpose\" value=\"Order id: $order_id\"/>
            <input type=\"submit\" value=\"Pay\"/>";
        }

        $output .= "</form></div>";

        echo $output;
        echo "<script language=\"javascript\" type=\"text/javascript\">document.getElementById('pokupo_form').submit();</script>";
    }
    exit();
}

function pokupo_callback()
{
    global $wpdb;

    $language = strtolower(substr(get_locale(), 0, 2));
    $is_callback = false;

    if ((isset($_GET['pokupo_result']) && $_GET['pokupo_result'] == '1'))
        $is_callback = true;

    // Process the callback.
    if ($is_callback == true) {
        $HTTP = $_POST;

        $LMI_PAYEE_PURSE = sanitize_key($HTTP['LMI_PAYEE_PURSE']);
        $LMI_PAYMENT_AMOUNT = sanitize_text_field($HTTP['LMI_PAYMENT_AMOUNT']);
        $LMI_PAYMENT_NO = sanitize_text_field($HTTP['LMI_PAYMENT_NO']);
        $LMI_SYS_TRANS_NO = sanitize_text_field($HTTP['LMI_SYS_TRANS_NO']);
        $LMI_MODE = sanitize_key($HTTP['LMI_MODE']);
        $LMI_SYS_INVS_NO = sanitize_text_field($HTTP['LMI_SYS_INVS_NO']);
        $LMI_SYS_TRANS_DATE = sanitize_text_field($HTTP['LMI_SYS_TRANS_DATE']);
        $LMI_PAYER_PURSE = sanitize_email($HTTP['LMI_PAYER_PURSE']);
        $LMI_PAYER_WM = sanitize_email($HTTP['LMI_PAYER_WM']);
        $LMI_HASH = sanitize_text_field($HTTP['LMI_HASH']);

        $sessionid = sanitize_key($HTTP['SESSION_ID']);

        $shop_id = get_option('pokupo_shop_id');
        $merch_key = get_option('pokupo_notification_password');
        $order = $LMI_PAYMENT_NO;
        $err = FALSE;
        $totalprice = $wpdb->get_var("SELECT totalprice FROM " . WPSC_TABLE_PURCHASE_LOGS . " WHERE sessionid = '$sessionid' LIMIT 1;");

        if ($totalprice)$totalprice = number_format($totalprice, 2, '.', '');

        if (isset($HTTP['LMI_PREREQUEST']) && (int)$HTTP['LMI_PREREQUEST'] == 1) {
            echo 'YES'; die;
        } else {
            if ($shop_id!=$LMI_PAYEE_PURSE) {
                $err = TRUE;
                $notes = 'Declined. Incorrect LMI_PAYEE_PURSE';
            }
            if ($totalprice!=$LMI_PAYMENT_AMOUNT) {
                $err = TRUE;
                $notes = 'Declined. Incorrect LMI_PAYMENT_AMOUNT';
            }
            if (isset($LMI_SECRET_KEY)) {
                if ($LMI_SECRET_KEY != $merch_key) {
                    $err = TRUE;
                    $notes = 'Declined. Incorrect LMI_SECRET_KEY';
                }
            } else {
                $CalcHash = md5($LMI_PAYEE_PURSE . $LMI_PAYMENT_AMOUNT . $LMI_PAYMENT_NO . $LMI_MODE . $LMI_SYS_INVS_NO . $LMI_SYS_TRANS_NO . $LMI_SYS_TRANS_DATE . $merch_key . $LMI_PAYER_PURSE . $LMI_PAYER_WM);
                if ($LMI_HASH != strtoupper($CalcHash)) {
                    $err = TRUE;
                    $notes = 'Declined. Incorrect LMI_HASH';
                }
            }
        }

        if (!$err) {
            $data = array(
                'processed' => 2,
                'transactid' => $order,
                'date' => time(),
            );
            wpsc_update_purchase_log_details($sessionid, $data, 'sessionid');
            transaction_results($sessionid, false, $order);
            echo 'YES'; die;
        } else {
            $wpdb->query("UPDATE " . WPSC_TABLE_PURCHASE_LOGS . " SET  date = '" . time() . "', notes = '" . $notes . "' WHERE sessionid = " . $sessionid . " LIMIT 1");
            echo 'ERROR: '.$notes; die;
        }

        exit();
    }
}


// Function used to translate returned sessionid POST variable into the recognised GET variable for the transaction results page.
function pokupo_results()
{
    if (isset($_POST['SESSION_ID'])) {
        $_GET['sessionid'] = sanitize_key($_POST['SESSION_ID']);
    }
}


add_action('init', 'pokupo_callback');
add_action('init', 'pokupo_results');