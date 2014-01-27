EBFacebookBundle
===========

1. Add the following lines in your composer.json:

    ```json
    {
        "require": {
            "friendsofsymfony/facebook-bundle": "dev-master",
            "friendsofsymfony/user-bundle": "dev-master",
            "earlybirds/facebook-bundle": "dev-master"
        }
    }
    ```

2. Run the composer to download the bundle:

    ```bash
    $ php composer.phar update
    ```
	
3. Add bundles to your application's kernel:

    ```php
    // app/ApplicationKernel.php
    public function registerBundles()
    {
        return array(
            // ...
            new FOS\FacebookBundle\FOSFacebookBundle(),
            new FOS\UserBundle\FOSUserBundle(),
            new EB\FacebookBundle\EBFacebookBundle(),
            // ...
        );
    }
    ```

4. Add the following routes to your application and point them at actual controller actions

    ```yaml
    #app/config/routing.yml
    eb_facebook:
        resource: "@EBFacebookBundle/Resources/config/routing.xml"
    ```

    ```xml
    <!-- app/config/routing.xml -->
    <import resource="@EBFacebookBundle/Resources/config/routing.xml"/>
    ```

5. Configure the `eb_facebook` service in your config:

    ```yaml
    #app/config/config.yml
    framework:
        translator: ~

    eb_facebook:
        app_id: 123456879 #Facebook application ID
        secret: s3cr3t #Facebook application secret
        templates:
            layout:  AcmeDemoBundle::layout.html.twig #Your personnal layout
            home:  AcmeDemoBundle:Demo:home.html.twig #Your personnal home view
            register:  AcmeDemoBundle:Demo:register.html.twig #Your personnal register view
        permissions: [email, user_birthday, user_location] #(Optional) Permissions of the app, if not configured set to default permissions
        fixcookie: https://host_of_facebook_application/fixcookie.php #(Optional) Url to a fix script to debug safari iframe on Facebook
        tab_url: https://www.facebook.com/MYCOMPANY/app_99999999999 #(Optional) Url of your Facebook tab page
        user_class: Acme\DemoBundle\Entity\User #(Optional) Namespace of your own Entity User class, default : EB\FacebookBundle\Entity\User
        form_class: Acme\DemoBundle\Form\UserType #(Optional) Namespace of your own Form User class, default : EB\FacebookBundle\Form\UserType
        translation: AcmeDemoBundle #(Optional) Change the translation domain, default : EBFacebookBundle
        culture: en_EN #(Optional) Facebook language, default : fr_FR
        
    ```

    If you have configured the fixcookie url, add to the web folder, the following PHP script

    ```php
    // web/fixcookie.php
    <?php
        setcookie('my_app_name', 'true'); //You app name

        header('Location:https://www.facebook.com/MYCOMPANY/app_99999999999'); //Url of your Facebook tab page
    ?>
    ```

6. Update the base:

    ```bash
    $ php app/console doctrine:schema:update --force
    ```

7. Install assets:

    ```bash
    $ php app/console assets:install web
    ```

    In dev you can use the symlink option

    ```bash
    $ php app/console assets:install --symlink
    ```

8. In prod you can dump assets:

    ```bash
    $ php app/console assetic:dump --env=prod --no-debug
    ```

9. Add this configuration if you want to use the security component:

    ```yaml
    # app/config/security.yml
    security:
        encoders:
            Symfony\Component\Security\Core\User\User: plaintext
            FOS\UserBundle\Model\UserInterface: sha512

        role_hierarchy:
            ROLE_USER:       ROLE_FACEBOOK
            ROLE_ADMIN:       ROLE_USER
            ROLE_SUPER_ADMIN: [ROLE_USER, ROLE_ADMIN, ROLE_ALLOWED_TO_SWITCH]

        providers:
            fos_userbundle:
                id: fos_user.user_provider.username_email
            facebook:
                id: eb_facebook.provider

        firewalls:
            dev:
                pattern:  ^/(_(profiler|wdt)|css|images|js)/
                security: false

            main:
                pattern: ^/
                fos_facebook:
                    app_url: "https://www.facebook.com/MYCOMPANY/app_99999999999" #You facebook app url
                    server_url: "https://host_of_facebook_application" #Url of your server url
                    check_path: _security_check
                    provider: facebook
                    default_target_path: eb_facebook_register
                logout:
                    path:   _security_logout
                    invalidate_session: false
                anonymous:    true
            
        access_control:
            - { path: ^/register, role: [ROLE_FACEBOOK] }
            - { path: ^/user_count, role: [ROLE_FACEBOOK] }
            - { path: ^/game, role: [ROLE_FACEBOOK] } #The route of the third page of the app (after home and validation of the form)
        
    ```

10. Create your layout with your own logic extending the EBFacebook layout

    ```html+jinja
    <!-- src/Acme/DemoBundle/Resources/views/layout.html.twig -->
    {% extends 'EBFacebookBundle::layout.html.twig' %}

    {% block stylesheets %}
        {{ parent() }}
        {% stylesheets 'bundles/acmedemo/css/style.css'
           filter='cssrewrite'
        %}
        <link rel="stylesheet" href="{{ asset_url }}" />
        {% endstylesheets %}
    {% endblock %}

    {% block javascripts %}
        {{ parent() }}
        {% javascripts 'bundles/acmedemo/js/ebfacebook.js'
        %}
        <script src="{{ asset_url }}" type="text/javascript"></script>
        {% endjavascripts %}
    {% endblock %}

    {% block title %}My application faceook title{% endblock title %}

    {% block footer_content %}
    Footer content example
    {% endblock footer_content %}
    ```

11. Create your home view with your own logic extending YOUR layout you have just created

    ```html+jinja
    <!-- src/Acme/DemoBundle/Resources/views/Demo/home.html.twig -->
    {% extends 'AcmeDemoBundle::layout.html.twig' %}

    {% block body %}
    <div class="home-wrap">
        <div class="home-text">
            My home text
        </div>
        {{ facebook_custom_login_button() }} <!-- Twig helper to render a custom facebook login button -->
    </div>

    {% endblock %}
    ```

12. Create your register view with your own logic extending the EBFacebook register view

    ```html+jinja
    <!-- src/Acme/DemoBundle/Resources/views/Demo/register.html.twig -->
    {% extends 'EBFacebookBundle:Facebook:register.html.twig' %}

    {% block form %}
    <h1>Register to continue</h1>

    <!-- I you don't want to append an asterisk to all form label, add the `noasterisk` class to your form -->
    <form method="post" action="{{ path('eb_facebook_register') }}" {{ form_enctype(form) }} class="user-form"> <!-- Add the user-form class to have the javascript form validation -->
        <div class="form-header">
            <div class="form-title">Fill the fields</div>
            <div class="form-notice">* mandatory fields</div>
        </div>
        <div class="form-errors">{{ form_errors(form) }}</div>
        <div class="form-fields">
            <div class="form-element">{{ form_row(form.lastname) }}</div>
            <div class="form-element">{{ form_row(form.firstname) }}</div>
            <div class="form-element">{{ form_row(form.birthday) }}</div>
            <div class="form-element">{{ form_row(form.email) }}</div>
            <div class="form-element">{{ form_row(form.address) }}</div>
            <div class="form-element">{{ form_row(form.zipcode_fr) }}</div>
            <div class="form-element">{{ form_row(form.city) }}</div>
            <div class="form-element">{{ form_row(form.phone) }}</div>
            <div class="options">
                <div class="form-element noasterisk">{{ form_row(form.readConditions) }}</div> <!-- You can add the class noasterisk to a specitic field like this -->
                <div class="offers">
                    <span>I accept to receive offers</span>
                    <div class="form-element offer">{{ form_row(form.offersEmail) }}</div>
                    <div class="form-element offer">{{ form_row(form.offersSms) }}</div>
                </div>
            </div>
        </div>
        <div class="form-submit">
            <input type="submit" value="Validate" class="small" />
        </div>
    </form>
    {% endblock %}
    ```

13. Create your game view with your own logic extending YOUR layout

    ```html+jinja
    <!-- src/Acme/DemoBundle/Resources/views/Demo/game.html.twig -->
   {% extends 'AcmeDemoBundle::layout.html.twig' %}

    {% block body %}
        <!-- Just add the class fbInvit to any element of your page to add a click event that open Facebook apprequests dialog -->
        <!-- You need to fill the data tag to customize text of the widget -->
        <input type="button" value="Invit friends" class="fbInvit" data-message="..." data-path="{{ path('eb_facebook_user_count') }}" />
        <!-- Just add the class fbPostWall to any element of your page to add a click event that open Facebook feed dialog -->
        <!-- You need to fill the data tag to customize text of the widget -->
        <input type="button" value="Publish on my wall" class="fbPostWall" data-link="..." data-caption="..." data-description="..." />
    {% endblock %}
    ```

14. Create the action for the game view in your controller, you have to name your route "game"

    ```php
    // src/Acme/DemoBundle/Controller/DemoController.php
    
    class DemoController extends Controller
    {
        /**
         * @Route("/game", name="game") //You have to name your route "game"
         * @Template()
         */
        public function gameAction()
        {
            /*
                Some code
            */
            return array(
            );
        }
    }
    ```