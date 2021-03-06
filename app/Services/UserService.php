<?php
namespace App\Services;

use App\Repositories\Role\RoleInterface;
use App\Repositories\User\UserInterface;
use Illuminate\Support\Facades\Auth;

class UserService
{
    /**
     * @var UserInterface
     */
    public $userRepository;

    /**
     * @var RoleInterface
     */
    public $roleRepository;

    public function __construct(UserInterface $userRepository, RoleInterface $roleRepository)
    {
        $this->userRepository = $userRepository;
        $this->roleRepository = $roleRepository;
    }

    /**
     * Ensure that user has employee rule
     * @param $user
     * @throws \Exception
     */
    public function onlyEmployee($user)
    {
        //Ensure that selected employee has employee rule
        if(!$user->hasRole('employee'))
        {
            throw new \Exception(trans('reports.no_employee'));
        }
    }

    /**
     * return selected user if user attempting is admin
     * or return logged in user, Only admin can pick whatever id they like
     * @param $userId
     * @return integer userId
     */
    public function getLoggedOrSelected($userId)
    {
        if (!Auth::user()->hasRole('admin'))
            return Auth::user();

        return $this->userRepository->getUserById($userId);
    }

    /**
     * Get User Role From Type
     * @param $type
     * @return mixed
     */
    public function getRoleFromType($type)
    {
        if(strtolower($type) == 'admin')
        {
            //Get admin role
            return $this->roleRepository->getRoleByName('admin');
        }

        //Otherwise, return employee role
        return $this->roleRepository->getRoleByName('employee');
    }

    /**
     * Generates dataTable controllers
     * @param $user user to add controllers for
     * @param $isLoggedAdmin true if current logged in user has role admin
     * @return string
     */
    public function dataTableControllers($user, $isLoggedAdmin)
    {
        $actions = '<a href="' . route('bonus.index', [$user->id]) . '" class="btn btn-xs btn-success"><i class="glyphicon glyphicon-star"></i>' . trans('bonuses.title') . '</a> ';
        $actions .= '<a href="' . route('defect.index', [$user->id]) . '" class="btn btn-xs btn-warning"><i class="glyphicon glyphicon-remove"></i>' . trans('defects.title') . '</a> ';
        $actions .= '<a href="' . route('report.user.index', [$user->id]) . '" class="btn btn-xs btn-info"><i class="glyphicon glyphicon-file"></i>' . trans('reports.reports') . '</a> ';

        //Delete form, show if admin
        if ($isLoggedAdmin) {
            $actions .= "<form class='delete-form' method='POST' action='" . route('user.destroy', $user->id) . "'>"
                . csrf_field() .
                "<input type='hidden' name='_method' value='DELETE'/>
                        <button type='submit' class='btn btn-xs btn-danger main_delete'>
                            <i class='glyphicon glyphicon-trash'></i> " . trans('general.delete') . "
                        </button>
                    </form>";
        }

        return $actions;
    }
}