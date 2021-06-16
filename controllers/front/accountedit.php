<?php
/**
 * BINSHOPS
 *
 * @author BINSHOPS - contact@binshops.com
 * @copyright BINSHOPS
 * @license https://www.binshops.com
 */

require_once dirname(__FILE__) . '/../AbstractRESTController.php';

class BinshopsrestAccounteditModuleFrontController extends AbstractRESTController
{

    protected function processGetRequest()
    {
        $this->ajaxRender(json_encode([
            'success' => true,
            'message' => 'GET not supported on this path'
        ]));
        die;
    }

    protected function processPostRequest()
    {
        $_POST = json_decode(Tools::file_get_contents('php://input'), true);

        $psdata = "";
        $messageCode = 0;
        $success = true;
        $firstName = Tools::getValue('firstName');
        $lastName = Tools::getValue('lastName');
        $email = Tools::getValue('email');
        $password = Tools::getValue('password');
        $gender = Tools::getValue('gender');
        $newsletter = Tools::getValue('newsletter');

        if (empty($email)) {
            $psdata = "An email address required";
            $messageCode = 301;
        } elseif (!Validate::isEmail($email)) {
            $psdata = "Invalid email address";
            $messageCode = 302;
        } elseif (empty($password)) {
            $psdata = 'Password is not provided';
            $messageCode = 303;
        } elseif (!Validate::isPasswd($password)) {
            $psdata = "Invalid Password";
            $messageCode = 304;
        } elseif (empty($firstName)) {
            $psdata = "First name required";
            $messageCode = 305;
        } elseif (empty($lastName)) {
            $psdata = "Last name required";
            $messageCode = 306;
        } elseif (empty($gender)) {
            $psdata = "gender required";
            $messageCode = 307;
        } else {
            $guestAllowedCheckout = Configuration::get('PS_GUEST_CHECKOUT_ENABLED');
            $cp = new CustomerPersister(
                $this->context,
                $this->get('hashing'),
                $this->getTranslator(),
                $guestAllowedCheckout
            );
            try {
                $customer = new Customer($this->context->customer->id);
                $customer->firstname = $firstName;
                $customer->lastname = $lastName;
                $customer->email = $email;
                $customer->id_gender = $gender;
                $customer->id_shop = (int)$this->context->shop->id;
                $customer->newsletter = $newsletter;

                $clearTextPassword = Tools::getValue('password');
                $newPassword = Tools::getValue('new_password');

                $status = $cp->save(
                    $customer,
                    $clearTextPassword,
                    $newPassword,
                    true
                );

                if ($status) {
                    $psdata = array(
                        'registered' => $status,
                        'message' => 'User updated successfully',
                        'customer_id' => $customer->id,
                        'session_data' => (int)$this->context->cart->id
                    );
                } else {
                    $psdata = array(
                        'registered' => $status,
                        'message' => 'password incorrect',
                        'customer_id' => $customer->id,
                        'session_data' => (int)$this->context->cart->id
                    );
                }
            } catch (Exception $exception) {
                $messageCode = 300;
                $psdata = $exception->getMessage();
                $success = false;
            }
        }

        $this->ajaxRender(json_encode([
            'success' => $success,
            'code' => $messageCode,
            'psdata' => $psdata
        ]));
        die;
    }

    protected function processPutRequest()
    {
        $this->ajaxRender(json_encode([
            'success' => true,
            'message' => 'put not supported on this path'
        ]));
        die;
    }

    protected function processDeleteRequest()
    {
        $this->ajaxRender(json_encode([
            'success' => true,
            'message' => 'delete not supported on this path'
        ]));
        die;
    }
}
