<?php

namespace Behat\DrushExtension\Context;

use Behat\DrushExtension\Drush;

use Behat\MinkExtension\Context\MinkContext;

/**
 * Drush aware Mink Context.
 */
class DrushMinkContext extends MinkContext implements DrushAwareInterface
{
    /**
     * Drush instance, set by DrushAwareInitializer.
     */
    private $drush;

    /**
     * Current authenticated user.
     *
     * A value of FALSE denotes an anonymous user.
     */
    protected $loggedInUser = FALSE;

    /**
     * Keep track of all users that are created so they can easily be removed.
     */
    protected $users = array();

    /**
     * Basic auth username and password.
     */
    protected $basic_auth;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context object.
     *
     * @param array $parameters.
     *   Context parameters (set them up through behat.yml or behat.local.yml).
     */
    public function __construct(array $parameters) {
      if (isset($parameters['basic_auth'])) {
        $this->basic_auth = $parameters['basic_auth'];
      }
    }

    /**
     * Sets Drush instance.
     *
     * @param string $drush
     */
    public function setDrush(Drush $drush) {
      $this->drush = $drush;
    }

    /**
     * Returns Drush instance.
     *
     * @return string
     */
    public function getDrush() {
      return $this->drush;
    }


    /**
     * Run before every scenario.
     *
     * @BeforeScenario
     */
    public function beforeScenario($event) {
      if (isset($this->basic_auth)) {
        $driver = $this->getSession()->getDriver();
        if ($driver instanceof \Behat\Mink\Driver\Selenium2Driver) {
          // Continue if this is a Selenium driver, since this is handled in
          // locatePath().
        }
        else {
          // Setup basic auth.
          $this->getSession()->setBasicAuth($this->basic_auth['username'], $this->basic_auth['password']);
        }
      }
    }

    /**
     * Override MinkContext::locatePath() to work around Selenium not supporting
     * basic auth.
     */
    protected function locatePath($path) {
      $driver = $this->getSession()->getDriver();
      if ($driver instanceof \Behat\Mink\Driver\Selenium2Driver && isset($this->basic_auth)) {
        // Add the basic auth parameters to the base url. This only works for
        // Firefox.
        $startUrl = rtrim($this->getMinkParameter('base_url'), '/') . '/';
        $startUrl = str_replace('://', '://' . urlencode($this->basic_auth['username']) . ':' . urlencode($this->basic_auth['password']) . '@', $startUrl);
        return 0 !== strpos($path, 'http') ? $startUrl . ltrim($path, '/') : $path;
      }
      else {
        return parent::locatePath($path);
      }
    }

    /**
     * Remove users created during this scenario, including the content they created.
     *
     * @AfterScenario
     */
    public function removeTestUsers($event) {
      // Remove any users that were created.
      if (!empty($this->users)) {
        foreach ($this->users as $user) {
          $command = sprintf('user-cancel --yes %s --delete-content', $user->name);
          $this->getDrush()->run($command);
          $this->printDebug(sprintf('cancelled user '. $user->name));
        }
      }
    }

    /**
     * Logs out the current user, if logged in.
     *
     * @Given /^I am an anonymous user$/
     * @Given /^I am logged out$/
     */
    public function logout() {
      // Verify the user is logged out.
      if ($this->loggedInUser) {
        $this->getSession()->visit($this->locatePath('/user/logout'));
        $this->loggedInUser = FALSE;
      }
    }

    /**
     * Creates and authenticates a user with the given role via Drush.
     *
     * @Given /^I am logged in as a user with the "([^"]*)" role$/
     */
    public function loginAsUserWithRole($role_name) {
      // Check if a user with this role is already logged in.
      if ($this->loggedInUser && in_array($role_name, $this->loggedInUser->roles)) {
        return TRUE;
      }

      $account = $this->drushCreateUser(array($role_name));
      $this->login($account);
    }

    /**
     * Authenticates a user. This is both a Given and a utility function.
     *
     * @Given /^I am logged in as "([^"]*)" with the password "([^"]*)"$/
     */
    public function login($account, $password = NULL) {
      if (!is_object($account)) {
        $username = $account;
        $account = new \stdClass();
        $account->name = $username;
        $account->pass = $pass;
      }

      // Check if logged in.
      if ($this->loggedInUser && ($this->loggedInUser->name != $account->name)) {
        $this->logout();
      }

      $this->getSession()->visit($this->locatePath('/user'));

      $element = $this->getSession()->getPage()->find('css', '#user-login');
      $element->fillField('edit-name', $account->name);
      $element->fillField('edit-pass', $account->pass);
      $submit = $element->findButton('Log in');
      if (empty($submit)) {
        throw new \Exception('No submit button at ' . $this->getSession()->getCurrentUrl());
      }

      // Log in.
      $submit->click();

      // If a logout link is found, we are logged in. While not perfect, this is
      // how Drupal SimpleTests currently work as well.
      if (!$this->getSession()->getPage()->findLink('Log out')) {
        $this->showLastResponse();
        $this->printDebug($this->getSession()->getCurrentUrl());
        throw new \Exception("Failed to log in as user \"{$account->name}\".");
      }

      // If we don't have a full account object, get it.
      if (empty($account->uid)) {
        $pass = $account->pass;
        $account = $this->drushUserInfo($account->name);
        $account->pass = $pass;
      }

      $this->loggedInUser = $account;
    }

    /**
     * Copied from drush_generate_password.  Generate a random string of,
     * suitable for mock user names and passwords.
     */
    protected function randomString($length = 10) {
      // This variable contains the list of allowable characters for the
      // password. Note that the number 0 and the letter 'O' have been
      // removed to avoid confusion between the two. The same is true
      // of 'I', 1, and 'l'.
      $allowable_characters = 'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';

      // Zero-based count of characters in the allowable list:
      $len = strlen($allowable_characters) - 1;

      // Declare the password as a blank string.
      $pass = '';

      // Loop the number of times specified by $length.
      for ($i = 0; $i < $length; $i++) {

        // Each iteration, pick a random character from the
        // allowable string and append it to the password:
        $pass .= $allowable_characters[mt_rand(0, $len)];
      }

      return $pass;
    }

    /**
     * Create a user with a given set of roles.
     *
     * @param array $roles
     *   Array of role names to assign to user.
     *
     * @return object|false
     *   A stub user object, as returned by parseDrushUserInfoString().
     */
    protected function drushCreateUser(array $roles = array()) {
      $name = $this->randomString(8);
      $mail = $name . '@example.com';
      $pass = $this->randomString(8);

      $command = sprintf('user-create "%s" --password="%s" --mail="%s" --pipe', $name, $pass, $mail);

      if ($result = $this->getDrush()->run($command)) {
        $account = $this->parseDrushUserInfoString($result);
      }
      else {
        throw new \Exception("Failed to create user with name \"{$name}\" and pass \"{$pass}\".");
      }

      if (empty($account->uid)) {
        throw new \Exception("Failed to create user with name \"{$account->name}\" and pass \"{$account->pass}\".");
      }

      // Add user to list of users to be removed later.
      $account->pass = $pass;
      $this->users[$account->uid] = $account;

      foreach ($roles as $role) {
        if (!in_array($role, $account->roles)) {
          $command = sprintf('user-add-role "%s" --name="%s" --pipe', $role, $account->name);
          if ($result = $this->getDrush()->run($command)) {
            $account = $this->parseDrushUserInfoString($result);
          }
          else {
            throw new \Exception("Failed to add role \"{$role}\" to user with name \"{$account->name}\"");
          }
        }
      }

      // Update user list.
      $account->pass = $pass;
      $this->users[$account->uid] = $account;

      return $account;
    }

    /**
     * Use drush to retrieve information about a user by name.
     */
    protected function drushUserInfo($name) {
      $command = sprintf('user-info "%s" --pipe', $name);
      if ($result = $this->getDrush()->run($command)) {
        return $this->parseDrushUserInfoString($result);
      }
      else {
        throw new \Exception("Failed to retrieve info for user with name \"{$name}\"");
      }
    }

    /**
     * Parse the info string returned from the Drush user-* commands when used
     * with the --pipe flag.
     *
     * @param string $string String returned from `drush user-* --pipe` commands.
     *   This string should look like name,uid,mail,status,"role,another role,etc"
     *
     * @return An object containing properties of name, uid, mail, status and roles.
     */
    protected function parseDrushUserInfoString($string) {
      $account = new \stdClass();
      list($account->name,
           $account->uid,
           $account->mail,
           $account->status,
           $account->roles) = explode(",", trim($string), 5);
      $account->roles = explode(',', trim($account->roles, '"'));

      return $account;
    }

}
