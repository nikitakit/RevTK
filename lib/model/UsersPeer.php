<?php
/**
 * Users.
 * 
 * @package RevTK
 * @author  Fabrice Denis
 */

class UsersPeer extends coreDatabaseTable
{
  // shortcut for ::getInstance()->getName()
  const
    TABLE = 'users';

  protected
    $tableName = 'users',
    $columns = array
    (
      'userid',
      'username',
      'password',
      'userlevel',
      'joindate',
      'lastlogin',
      'email',
      'location',
      'timezone'
    );

  /**
   * Credential values as stored in `userlevel`.
   */
  const USERLEVEL_ADMIN = 9;
  const USERLEVEL_USER  = 1;

  /**
   * Get this peer instance to access the base methods.
   * This function must be copied in each peer class.
   */
  public static function getInstance()
  {
    return coreDatabaseTable::_getInstance(__CLASS__);
  }

  /**
   * Find one user by given criteria and return all data for it.
   * 
   * Returns dates as unix timestamps (can be formatted by php)
   * with the prefix "ts_"
   * 
   * @param  string  $criteria    Column to search
   * @param  mixed   $value       Value to match
   * @return array   User array record, or false
   */
  private static function getUserBy($criteria, $value)
  {
    $select = self::getInstance()->select(array(
      '*',
      'ts_joindate' => 'UNIX_TIMESTAMP(joindate)',
      'ts_lastlogin' => 'UNIX_TIMESTAMP(lastlogin)'));

    $select->where($criteria . ' = ?', $value)
           ->query();
           
    $user = self::$db->fetch();

    return $user;
  }
  
  /**
   * Get user by unique name.
   * 
   * @param string  $username
   */
  public static function getUser($username)
  {
    return self::getUserBy('username', $username);
  }
  
  /**
   * Get user by unique id.
   * 
   * @param mixed  $userid    Should be numeric, but string is ok
   */
  public static function getUserById($userid)
  {
    return self::getUserBy('userid', $userid);
  }

  /**
   * Get user by email address.
   * 
   * @param string  $email
   */
  public static function getUserByEmail($email)
  {
    return self::getUserBy('email', $email);
  }
  
  /**
   * Get user id for name
   *
   * @return int  User id or false
   */
  public static function getUserId($username)
  {
    $select = self::getInstance()->select('userid')->where('username = ?', $username)->query();
    if ($row = self::$db->fetch())
    {
      return (int) $row['userid'];
    }
    return false;
  }

  /**
   * Lastlogin setter.
   * 
   * Sets lastlogin time to NOW() by default.
   *
   * @param int  $userid
   */
  public static function setLastlogin($userid, $timestamp = null)
  {
    return self::updateUser($userid, array('lastlogin' => $timestamp===null ? new coreDbExpr('NOW()') : $timestamp));
  }

  /**
   * Password setter.
   *
   * @param int     $userid
   * @param string  $password  Mangled password!
   */
  public static function setPassword($userid, $password)
  {
    return self::updateUser($userid, array('password' => $password));
  }

  /**
   * Checks if username is registered.
   *
   * @return boolean True if username is already registered.
   */
  public static function usernameExists($username)
  {
    return (self::getInstance()->count('username = ?', $username) > 0);
  }

  /**
   * Create record.
   * 
   * Required information:
   *   username
   *   password  (already mangled)
   *   email
   *   location
   * 
   * Optional:
   *   userlevel
   *   
   * @param array $userinfo  Assoc.array of form registration data
   */
  public static function createUser(array $userinfo)
  {
    $userdata = array(
      'username'      => $userinfo['username'],
      'password'       => $userinfo['password'],
      'email'         => $userinfo['email'],
      'location'       => $userinfo['location'],
      'joindate'      => new coreDbExpr('NOW()')
    );

    // may be explicitly set by maintenance tools
    if (isset($userinfo['userlevel'])) {
      $userdata['userlevel'] = $userinfo['userlevel'];
    }

    return self::getInstance()->insert($userdata);
  }

  /**
   * Update record.
   * 
   * Array must contain keys matching table columns,
   * values must be trimmed and validated already.
   * 
   * @param
   * @return
   */
  public static function updateUser($userid, $userdata)
  {
    return self::getInstance()->update($userdata, 'userid = ?', $userid);
  }
}
