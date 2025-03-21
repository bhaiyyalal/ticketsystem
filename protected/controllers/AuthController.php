<?php

class AuthController extends Controller {

    public $user_data;

    protected function beforeAction($event) {

        $this->user_data = Yii::app()->session['user_data'];
        if (!isset($this->user_data) && empty($this->user_data) && !isset($_GET['code']) && empty($_GET['code'])) {

            if (!in_array(ucfirst(Yii::app()->controller->action->id), array('Index', 'Recover_password', 'Logout'))) {
                $this->redirect(Yii::app()->request->baseUrl . '/auth');
            }
        }
        return true;
    }

    /**
     * Declares class-based actions.
     */
    public function actions() {
        return array(
            // captcha action renders the CAPTCHA image displayed on the contact page
            'captcha' => array(
                'class' => 'CCaptchaAction',
                'backColor' => 0xFFFFFF,
            ),
            // page action renders "static" pages stored under 'protected/views/site/pages'
            // They can be accessed via: index.php?r=site/page&view=FileName
            'page' => array(
                'class' => 'CViewAction',
            ),
        );
    }

    /**
     * This is the default 'index' action that is invoked
     * when an action is not explicitly requested by users.
     */
    public function actionIndex() {
        // renders the view file 'protected/views/site/index.php'
        // using the default layout 'protected/views/layouts/main.php'

        if (Yii::app()->session['user_data']) {
            if ($this->user_data['user_last_login_time'] == NULL || $this->user_data['user_last_login_time'] == '0000-00-00 00:00:00') {
                $this->redirect(Yii::app()->request->baseUrl . '/auth/setPassword');
                exit;
            }

            Yii::app()->user->setFlash('Already logged in', 'Already logged in.');
            if (isset($this->user_data["user_id"])) {

                if ($_SESSION['seturl'] == 1 && $_SESSION["mainurl"] != "") {

                    $mainurl = $_SESSION["mainurl"];

                    $this->redirect(Yii::app()->request->baseUrl . $mainurl);
                } else {

                    $this->redirect(Yii::app()->request->baseUrl . '/dashboard');
                }
            }
            exit;
        }
        $this->actionLogin();
    }

    /**
     * This is the action to handle external exceptions.
     */
    public function actionError() {
        if ($error = Yii::app()->errorHandler->error) {
            if (Yii::app()->request->isAjaxRequest) {
                echo"<pre>";
                print_r($error);
                echo"</pre>";
                die;
            } else {
                if ($error['code'] == 404 || $error['code'] == 400) {
                    $this->render('404');
                    die;
                }
            }
            echo"<pre>";
            print_r($error['code']);
            echo"</pre>";
            // die;
        }
    }

    /**
     * Displays the login page
     */
    public function actionLogin() {

        $model = new LoginForm;
        // if it is ajax validation request
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'login-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }

        // collect user input data
        if (isset($_POST['LoginForm'])) {
            $model->attributes = $_POST['LoginForm'];
            // validate user input and redirect to the previous page if valid
            if ($model->validate() && $model->login()) {

                $this->user_data = Yii::app()->session['user_data'];
                // if user login first time then it must ask for change the password
                if ($this->user_data['user_last_login_time'] == NULL || $this->user_data['user_last_login_time'] == '0000-00-00 00:00:00') {
                    $model->updateLastLoginTime($this->user_data['user_id']);
                    $this->redirect(Yii::app()->request->baseUrl . '/auth/setPassword');
                } else {
                    $model->updateLastLoginTime($this->user_data['user_id']);
                    $this->redirect(Yii::app()->user->returnUrl);
                }
            }
        }
        $this->layout = 'login_layout';
        // display the login form
        $this->render('new_login', array('model' => $model));
    }

    public function actionRecover_password() {

        $this->layout = 'login_layout';
        $model = new ForgetPassword;
        if (isset($_POST['ForgetPassword']['username'])) {

            $email = $_POST['ForgetPassword']['username'];

            $user_details = Users::model()->findByAttributes(array(trim('user_email') => trim($_POST['ForgetPassword']['username'])));
//            print_r($user_details);
//            die;
            if (!empty($user_details)) {
                $userdata['user_name'] = $user_details->user_name;
                $userdata['user_email'] = $user_details->user_email;
                $code = base64_encode($user_details->user_email);
                $userdata['link_to_reset_password'] = Utils::getBaseUrl() . "/auth/setPassword?code=" . $code;
                $userdata['link_expiry_time'] = 30;
                $template = Template::getTemplate('forgot_password_email_template');
                $subject = $template->template_subject;
                $message = $template->template_content;
                $subject = $this->replace($userdata, $subject);
                $message = $this->replace($userdata, $message);
                $user_details->forgot_password_code = $code;
                $user_details->forgot_pass_code_expiry = date('Y-m-d H:i:s');
                if ($user_details->update()) {

                    $this->SendMail($user_details->user_email, $user_details->user_name, $subject, $message);

                    Yii::app()->user->setFlash('type', 'success');
                    Yii::app()->user->setFlash('message', '\'Reset Password\' link has been sent on your requested Email ID successfully.');
                } else {

                    Yii::app()->user->setFlash('type', 'danger');
                    Yii::app()->user->setFlash('message', 'Operation failded due to lack of connectivity. Try again later!!!');
                }
            } else {
                Yii::app()->user->setFlash('type', 'danger');
                Yii::app()->user->setFlash('message', 'This Email ID doesn\'t exist. Please enter a valid Email ID.');
            }
        }

        $this->render('recover_password', array('model' => $model));
    }

    public function actionSetPassword() {
        $model = new ResetPassword;
        // if it is ajax validation request
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'reset_password') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }

        if (isset($_POST['ResetPassword'])) {
            $model->attributes = $_POST['ResetPassword'];


            $loginmodel = new LoginForm;

            // validate user input and redirect to the previous page if valid
            if ($model->validate()) {

                if (isset($_GET['code']) && !empty($_GET['code'])) {

                    $user_details = Users::model()->findByAttributes(array('forgot_password_code' => $_GET['code']));
                    $reset_time = strtotime($user_details->forgot_pass_code_expiry);
                    $current_time = strtotime(date('Y-m-d H:i:s'));
                    $time_diff = round(($current_time - $reset_time) / 60);

                    if ($time_diff <= 30) {

                        $user_details->user_password = md5($_POST['ResetPassword']['password']);
                        $user_details->forgot_password_code = "";
                        $user_details->updated_date = date('Y-m-d H:i:s');
                        if ($user_details->save()) {
                            $loginmodel->UpdateLastLoginTime($user_details->user_id);
                            $this->redirect(Yii::app()->request->baseUrl . '/auth');
                        }
                    } else {
                        Yii::app()->user->setFlash('error', "Reset Password may once used or expired , please try again !");
                    }
                } else {
                    $update_password = $model->reset_password($this->user_data['user_id']);
                    if ($update_password == 1) {
                        if ($loginmodel->UpdateLastLoginTime($this->user_data['user_id'])) {
                            $this->redirect(Yii::app()->request->baseUrl . '/auth');
                        } else {
                            $this->redirect(Yii::app()->request->baseUrl . '/auth/setPassword');
                        }
                    } else {
                        Yii::app()->user->setFlash('error', "Password could not be reset , please try again !");
                    }
                }
            } else {
                Yii::app()->user->setFlash('error', "Password could not be reset , please try again !");
            }
        }
        $this->layout = 'login_layout';
        // display the login form
        // Yii::app()->user->setFlash('error', "Again on same page");
        $this->render('reset_password', array('model' => $model, "error" => Yii::app()->user->getFlashes()));
    }

    public function actionLogout() {

        $loginmodel = new LoginForm;
        $loginmodel->UpdateLastLogoutTime(Yii::app()->session['user_data']['user_id']);
        unset(Yii::app()->session['user_data']);
        Yii::app()->user->logout();
        $this->redirect(Yii::app()->request->baseUrl . '/auth');
    }

    public function actionDashboard() {
        $user = Users::model()->findByAttributes(array('user_id' => Yii::app()->session['user_data']['user_id']));
        Yii::app()->session['user_data'] = $user;
        $this->user_data = Yii::app()->session['user_data'];
        $role_name = UserRoles::model()->getRoleName($user->user_role_type);
        Yii::app()->user->name = $role_name;
        $user_role_type = Yii::app()->session['user_data']['user_role_type'];
        $this->render('dashboard', $data);
    }

    public function actionUsersetting() {
        $model = Users::model()->findByPk(Yii::app()->session['user_data']['user_id']);

        if (isset($_REQUEST['reset_password']) && $_REQUEST['reset_password']) {
            $reset_password_flaf = true;
            $profile_flaf = false;
        } else {
            $reset_password_flaf = false;
            $profile_flaf = true;
        }

        if (isset($_POST['Users'])) {
            $model->attributes = $_POST['Users']['user_name'];
            if ($model->validate(array("user_name"))) {
                $model->user_name = $_POST['Users']["user_name"];
                $model->updated_date = date("Y-m-d H:i:s");
                if ($model->save()) {
                    Yii::app()->user->setFlash('success', "Profile Updated successfully.");
                } else {
                    Yii::app()->user->setFlash('error', "Please try again.");
                }
            }
        }

        $this->render('userSetting', array('model' => $model, 'user_edit_data' => $user_data, "message" => Yii::app()->user->getFlashes(), "reset_password" => $reset_password_flaf, "profile" => $profile_flaf));
    }

    public function actionUpdate_password() {
        $model = Users::model()->findByPk(Yii::app()->session['user_data']['user_id']);
        $error = 0;
        if (isset($_POST['old_password'])) {
            if (isset($_POST['old_password']) && $_POST['old_password'] != '') {
                if (!$this->actionCheckPassword($_POST['old_password'])) {
                    Yii::app()->user->setFlash('old_password', 'Please enter a valid Old Password.');
                    $error = 1;
                }
            } else {
                Yii::app()->user->setFlash('old_password', 'Please Enter Old Password.');
                $error = 1;
            }

            if (!isset($_POST['password']) || $_POST['password'] == '') {
                Yii::app()->user->setFlash('password', 'Please Enter New Password.');
                $error = 1;
            }

            if (!isset($_POST['re_password']) || $_POST['re_password'] == '') {
                Yii::app()->user->setFlash('re_password', 'Please Reconfirm New Password.');
                $error = 1;
            }

            if ($_POST['password'] != $_POST['re_password']) {
                Yii::app()->user->setFlash('re_password', 'Both password should be same.');
                $error = 1;
            }
            if ($error == 0) {
                $model->user_password = md5($_POST['password']);
                if ($model->save()) {
                    Yii::app()->user->setFlash('success', "Password Updated Successfully.");
                } else {
                    Yii::app()->user->setFlash('error', "Password could not be updated, please try again later!");
                    Yii::app()->user->setFlash('postData', $_POST);
                }
            } else {
                Yii::app()->user->setFlash('error', "Please correct following fields.");
                Yii::app()->user->setFlash('postData', $_POST);
            }
        }

        //#reset_password
        $this->redirect(array('usersetting', "reset_password" => true, "profile" => false));
        //$this->render('userSetting',array('model' => $model,"dept_list" => $admin_model->get_dept_list(), "acc_type" => $admin_model->get_account_type(), 'user_edit_data' => $user_data, "error"=> Yii::app()->user->getFlashes(),"reset_password"=>true,"profile"=>false));
    }

    public function actionCheckPassword($password) {
        if ($password != "") {
            $id = Yii::app()->session['user_data']['user_id'];
            $model = new Users;
            if ($model->checkPassword($password, $id)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

}
