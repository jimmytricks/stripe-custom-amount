<?php 

// Fire up composer
require_once('vendor/autoload.php');

// Set your secret key here
\Stripe\Stripe::setApiKey("YOUR_SECRET_KEY");

// Get the payment token ID submitted by the form:
$token = $_POST['stripeToken'];

// Backup of user info to send as meta data
$user_info = array("Name" => $_POST['name'], "Email" => $_POST['email']);


try {
// Createa a customer in stripe
  $customer = \Stripe\Customer::create(array(
    'email' => $_POST['email'],
    'card'  => $token,
  ));
  
// Charge the customer 
    $charge = \Stripe\Charge::create([
        'customer' => $customer->id,
        'amount' => ($_POST['amount']*100),
        'currency' => 'cad',
        'description' => 'Your Service: ' . $_POST['name'],
        'receipt_email' => $_POST['email'],
        'metadata' => $user_info,
    ]);

    // Redirect to thank you page after completing
    header('Location: /thank-you/');

    // Error handling
  } catch(\Stripe\Error\Card $e) {
    // Since it's a decline, \Stripe\Error\Card will be caught
    $body = $e->getJsonBody();
    $err  = $body['error'];

    session_start();
    // Pass the error to session for error handling

    $_SESSION['payment_error_msg'] = $err['message'];
    header('Location: /payment-unsuccessful/');
  } catch (\Stripe\Error\RateLimit $e) {
    // Too many requests made to the API too quickly

    session_start();    

    // Pass the error to session for error handling    
    $_SESSION['payment_error_msg'] = 'Too many requests made. Please wait and try again';
    header('Location: /payment-unsuccessful/');

  } catch (\Stripe\Error\InvalidRequest $e) {
    // Invalid parameters were supplied to Stripe's API
    session_start();    
    
    // Pass the error to session for error handling    
    $_SESSION['payment_error_msg'] = 'Invalid Parameters used in the request';
    header('Location: /payment-unsuccessful/');
    
  } catch (\Stripe\Error\Authentication $e) {
    // Authentication with Stripe's API failed
    // (maybe you changed API keys recently)
    session_start();    
    
    // Pass the error to session for error handling    
    $_SESSION['payment_error_msg'] = 'Incorrect API Keys';
    header('Location: /payment-unsuccessful/');

  } catch (\Stripe\Error\ApiConnection $e) {
    // Network communication with Stripe failed

    session_start();    
    
    // Pass the error to session for error handling    
    $_SESSION['payment_error_msg'] = 'Incorrect API Keys';
    header('Location: /payment-unsuccessful/');

  } catch (\Stripe\Error\Base $e) {
    // Display a very generic error to the user, and maybe send
    // yourself an email
    session_start();    
    
    // Pass the error to session for error handling    
    $_SESSION['payment_error_msg'] = 'Generic Stripe Error. Please send JTB an email';
    header('Location: /payment-unsuccessful/');

  } catch (Exception $e) {
    // Something else happened, completely unrelated to Stripe

    session_start();    
    
    // Pass the error to session for error handling    
    $_SESSION['payment_error_msg'] = 'Website error. Please send JTB an email';
    header('Location: /payment-unsuccessful/');
  }

?>