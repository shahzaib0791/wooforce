<?php


$client_id = get_option('wf_salesforce_client_id');
$client_secret = get_option('wf_salesforce_client_secret');
$redirect_uri = get_option('wf_salesforce_callback_url');

if(isset($_POST['wf_settings_fetch_auth_code'])) {
    if($_POST['wf_client_id'] !== "" && $_POST['wf_client_secret'] !== "" && $_POST['wf_redirect_uri'] !== "") {
        update_option('wf_salesforce_client_id', $_POST['wf_client_id']);
        update_option('wf_salesforce_client_secret', $_POST['wf_client_secret']);
        update_option('wf_salesforce_callback_url', $_POST['wf_redirect_uri']);
        wp_redirect("https://login.salesforce.com/services/oauth2/authorize?grant_type=authorization_code&client_id=" . $_POST['wf_client_id'] . "&client_secret=" . $_POST['wf_client_secret'] . "&response_type=code&redirect_uri=" . $_POST['wf_redirect_uri']);
    } else {
        echo "<h3 style='color: red; font-weight: bold; margin-top: 50px;'>Please fill all the fields with correct information</h3>";
    }
}

if(isset($_GET['code'])) {

    $body = array (
        'client_id' => $client_id,
        'client_secret'   => $client_secret,
        'redirect_uri' => $redirect_uri,
        'code' => $_GET['code'],
        'grant_type' => 'authorization_code',
    );

    $args = array (
        'body'        => $body,
        'timeout'     => '5',
        'redirection' => '5',
        'httpversion' => '1.0',
        'blocking'    => true,
        'headers'     => [],
        'cookies'     => [],
    );

    $fetch_access_token = wp_remote_post( 'https://login.salesforce.com/services/oauth2/token', $args);
    $request_body = json_decode(wp_remote_retrieve_body($fetch_access_token));
    $http_code = wp_remote_retrieve_response_code($fetch_access_token);
    if(isset($request_body->instance_url)) update_option('wf_salesforce_instance_url', $request_body->instance_url);
    if(isset($request_body->access_token)) update_option('wf_salesforce_token', $request_body->access_token);
    if(isset($request_body->refresh_token)) update_option('wf_salesforce_refresh_token', $request_body->refresh_token);
    wp_redirect($redirect_uri);
    exit;

//    if($http_code === 200) {
//        wp_redirect($redirect_uri . "?request_status=success");
//        exit;
//    } else {
//        wp_redirect($redirect_uri . "?request_status=failed");
//        exit;
//    }
//

}


if(isset($_POST['wf_fetch_users'])) {
    $fetch_sf_conts_accs = wp_remote_get(get_site_url() . '/cronjob_wooforce/fetch_contacts_accounts_lat.php');
}

if(isset($_POST['wf_fetch_quotes'])) {
    $fetch_sf_quos_opps = wp_remote_get(get_site_url() . '/cronjob_wooforce/fetch_opportunities_quotes.php');
    //print_r($fetch_sf_quos_opps);die;
}




?>

<div id="wpbody-content">
    <div class="wrap wf-wrap">
        <div class="wf-page-heading">
            <h1 class="wp-heading-inline"><?php echo esc_html(get_admin_page_title()); ?></h1>
            <hr class="wp-header-end">
        </div>

        <form class="wf-form" method="post">
            <h2 class="wf-form-heading">SalesForce App keys</h2>

            <div class="wf-options">
                <label for="wf_client_id">App Consumer Key:</label>
                <input type="text" name="wf_client_id" id="wf_client_id" value="<?php echo $client_id; ?>" />
            </div>
            <div class="wf-options">
                <label for="wf_client_secret">App Consumer Secret:</label>
                <input type="text" name="wf_client_secret" id="wf_client_secret" value="<?php echo $client_secret; ?>" />
            </div>
            <div class="wf-options">
                <label for="wf_redirect_uri">App Redirect URI:</label>
                <input type="text" name="wf_redirect_uri" id="wf_redirect_uri" value="<?php echo $redirect_uri; ?>" />
            </div>
            <div class="wf-submit">
                <div class="wf-fetch-data">
                    <button class="wf-fetch-btn" type="submit" name="wf_fetch_users">Fetch Users From SalesForce</button>
                    <button class="wf-fetch-btn" type="submit" name="wf_fetch_quotes">Fetch Quotes From SalesForce</button>
                </div>
               <!-- <span class="wf-success-msg"><?php //echo($token_request == 'success') ?  "Access token fetched successfully!" ($token_request == 'failed') ? : "Failed to fetch access token, please try again later!" : ""; ?></span> -->
                <?php if($fetch_sf_conts_accs || $fetch_sf_quos_opps) : ?>
                    <span class="wf-success-msg">Success!</span> 
                <?php endif; ?>
               <button class="wf-submit-btn" type="submit" name="wf_settings_fetch_auth_code">Fetch Access Code</button>
            </div>
        </form>
    </div>
</div>

<style>
    .wf-wrap {
        display: flex;
        flex-direction: column;
    }
    .wf-page-heading{
        display: flex;
        justify-content: center;
        padding: 20px
    }
    .wf-form {
        display: flex;
        flex-direction: column;
        justify-content: center;
        width: 100%;
    }
    .wf-options {
        width: 100%;
        margin-bottom: 20px;
    }
    .wf-options input {
        width: 100%;
        padding: 10px;
        margin-top: 5px;
        border-radius: 5px;
    }
    .wf-options label {
        width: 100%;
        margin: 5px
    }
    .wf-submit {
        display: flex;
        justify-content: space-between;
        margin-top: 75px;
        align-items: center;
    }
    .wf-fetch-data {
        display: flex;
        width: 25%;
    }
    .wf-fetch-btn {
        width: 100%;
        height: 40px;
        background-color: #2271b1;
        color: #FFFFFF;
        border-radius: 5px;
        border: none;
        cursor: pointer;
        margin-right: 20px;
    }
    .wf-submit-btn {
        width: 10%;
        height: 40px;
        background-color: #2271b1;
        color: #FFFFFF;
        border-radius: 5px;
        border: none;
        cursor: pointer;
    }
    .wf-success-msg {
        width: 25%;
        text-align: center;
        min-width: auto;
        height: auto;
        font-weight: bold;
        font-size: 18px;
        color: #71bf62;
    }
</style>