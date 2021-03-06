<?php

namespace App\Repositories\User;

use App\User;
use Illuminate\Database\Eloquent\Model;
use App\Repositories\BaseRepository;

/**
 * UserRepository is a class that contains common queries for users
 */
class UserRepository extends BaseRepository implements UserInterface
{

    /**
     * Holds user model
     * @var Model
     */
    protected $userModel;

    /**
     * UserRepository constructor.
     * Inject whatever passed model
     * @param Model $user
     */
    public function __construct(Model $user)
    {
        $this->userModel = $user;
    }

    /**
     * Create new User
     * @param $data array of key-value pairs
     * @return Model user data
     * @throws \Exception
     */
    public function create($data)
    {
        $user = $this->userModel->create($data);
        if (!$user) {
            throw new \Exception(trans('users.not_created'));
        }
        return $user;
    }

    /**
     * Retrieves user by id
     * @param $id
     * @return Model user
     * @throws \Exception
     */
    public function getUserById($id)
    {
        $user = $this->userModel->find($id);

        if(!$user)
        {
            throw new \Exception(trans('users.no_employee'));
        }
        return $user;
    }

    /**
     * Get All users with role employee
     * @return \App\User[]
     * @throws \Exception
     */
    public function getAllEmployees()
    {
        $employees = $this->userModel->whereHas('roles', function($q){
            $q->where('name','=','employee');
        })->get();

        if(!$employees || $employees->isEmpty())
        {
            throw new \Exception(trans('reports.no_employee'));
        }

        return $employees;
    }

    /**
     * Query scope that gets bonuses for a user
     * @param bool $isAdmin
     * @param Integer $loggedInUserId
     * @param Integer $sentUserId
     * @return mixed
     */
    public function getBonusesForUserScope($isAdmin, $loggedInUserId, $sentUserId)
    {
        //Get bonuses related to a user by userId
        $bonuses = $this->userModel->join('bonuses', 'users.id', 'bonuses.user_id')
            ->select(['bonuses.id', 'bonuses.description', 'bonuses.value', 'bonuses.created_at']);

        //Make sure that user can't see other users data
        if ($isAdmin) {
            return $bonuses->where('users.id', $sentUserId);
        }

        return $bonuses->where('users.id', $loggedInUserId);
    }

    /**
     * Get Users exclude role query scope
     * @param $roleId
     * @return mixed
     */
    public function getUsersForRoleScope($roleId)
    {
        $users = User::join('role_user', 'users.id', '=', 'role_user.user_id')
            ->join('employee_types', 'users.employee_type', '=', 'employee_types.id')
            ->where('role_user.role_id', '!=', $roleId)
            ->select(['users.id', 'users.name', 'users.email', 'employee_types.type']);

        return $users;
    }

    /**
     * Attach role to user
     * @param $user
     * @param $role
     */
    public function attachRole($user, $role)
    {
        $user->attachRole($role);
    }

    /**
     * Delete user from database
     * @param $id
     * @param string $attribute
     * @throws \Exception
     */
    public function destroy($id, $attribute="id")
    {
        if(!$this->userModel->where($attribute, '=', $id)->delete())
        {
            throw new \Exception('users.not_deleted');
        }
    }
}