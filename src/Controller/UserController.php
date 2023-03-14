<?php

namespace App\Controller;

use App\Entity\User;
use App\Services\PaginationManager;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\ArrayShape;
use Nzo\UrlEncryptorBundle\Encryptor\Encryptor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    const ITEMS_PER_PAGE = 10;
    private $plainPassword;
    private $pageManager;
    private $em;
    private $passwordHasher;
    private $mailer;
    private $encryptor;

    public function __construct(
        EntityManagerInterface $em, PaginationManager $pageManager, Encryptor $encryptor,
        UserPasswordHasherInterface $passwordHasher, MailerInterface $mailer
    )
    {
        $this->em = $em;
        $this->pageManager = $pageManager;
        $this->passwordHasher = $passwordHasher;
        $this->mailer = $mailer;
        $this->encryptor = $encryptor;
    }

    #[Route('/admin/user/form/new', name: 'user_new_form')]
    public function getNewUserForm(): Response
    {
        return new JsonResponse($this->getUserForm());
    }

    #[Route('/admin/user/new', name: 'create_user')]
    public function createUser(Request $request): Response
    {
        $data = $request->request;
        $resetPassword = $data->get('reset-password');
        $userId = $data->get('user-id');
        $user = $this->em->getRepository(User::class)->find($userId);

        if($user == null && $userId > 0)
        {
            $user = new User();
        }

        $response = [];

        if(!empty($data))
        {
            $user->setFirstName($this->encryptor->encrypt($data->get('first-name')));
            $user->setLastName($this->encryptor->encrypt($data->get('last-name')));
            $user->setEmail($this->encryptor->encrypt($data->get('email')));
            $user->setHashedEmail(md5($data->get('email')));

            // User Roles
            if(count($data->get('roles')) > 0)
            {
                $roles = [];

                foreach($data->get('roles') as $role)
                {
                    $roles[] = $role;
                }

                $user->setRoles($roles);
            }

            $this->em->persist($user);
            $this->em->flush();

            $getPassword = $this->setUserPassword($user->getId());

            $plainPwd = $getPassword['plainPassword'];
            $hashedPwd = $getPassword['hashedPassword'];

            $user->setPassword($hashedPwd);

            $this->em->persist($user);
            $this->em->flush();

            if($resetPassword == 'true')
            {
                // Send Email
                $body = '<table style="padding: 8px; border-collapse: collapse; border: none; font-family: arial, serif">';
                $body .= '<tr><td colspan="2">Hi '. $this->encryptor->decrypt($user->getFirstName()).',</td></tr>';
                $body .= '<tr><td colspan="2">&nbsp;</td></tr>';
                $body .= '<tr><td colspan="2">Please use the credentials below login to the Fluid Backend.</td></tr>';
                $body .= '<tr><td colspan="2">&nbsp;</td></tr>';
                $body .= '<tr>';
                $body .= '    <td><b>URL: </b></td>';
                $body .= '    <td><a href="https://'. $_SERVER['HTTP_HOST'] .'/admin">https://'. $_SERVER['HTTP_HOST'] .'/admin</a></td>';
                $body .= '</tr>';
                $body .= '<tr>';
                $body .= '    <td><b>Username: </b></td>';
                $body .= '    <td>'. $this->encryptor->decrypt($user->getEmail()) .'</td>';
                $body .= '</tr>';
                $body .= '<tr>';
                $body .= '    <td><b>Password: </b></td>';
                $body .= '    <td>'. $plainPwd .'</td>';
                $body .= '</tr>';
                $body .= '</table>';

                $email = (new Email())
                    ->from($this->getParameter('app.email_from'))
                    ->addTo($data->get('email'))
                    ->subject('Fluid Login Credentials')
                    ->html($body);

                $this->mailer->send($email);
            }

            $response['flash'] = '<b><i class="fas fa-check-circle"></i> User account successfully saved.<div class="flash-close"><i class="fa-solid fa-xmark"></i></div>';
            $response['user'] = $user->getFirstName() .' '. $user->getLastName();
        }

        return new JsonResponse($response);
    }

    #[Route('/admin/users/list', name: 'users_list')]
    public function getUsersList(Request $request): Response
    {
        $users = $this->em->getRepository(User::class)->adminFindAll();
        $results = $this->pageManager->paginate($users[0], $request, self::ITEMS_PER_PAGE);
        $pagination = $this->getPagination($request->get('page-id'), $results, '/admin/users/');

        $response = '
        <section class="px-4">
            <div class="row admin-header" style="left: 16.8%">
                <div class="col-12 col-md-6 text-truncate mt-1 pt-3 pb-3 ps-4">
                    <h4 class="text-truncate m-0">Users</h4>
                </div>
                <div class="col-12 col-sm-6 text-truncate mt-1 pt-3 pb-3 px-4">
                    <a 
                        type="button" 
                        class="float-end mb-2" 
                        data-action="click->admin--users#onClickCreateNew"
                    >
                        <i class="fa-regular fa-square-plus"></i>
                        Create New
                    </a>
                </div>
            </div>
            <div class="row">
                <div class="row border-bottom d-none d-md-flex pe-0 ">
                    <div class="col-1 fw-bold ps-4 text-truncate py-3 bg-white border-top border-left">
                        #ID
                    </div>
                    <div class="col-2 fw-bold text-truncate py-3 bg-white border-top">
                        First Name
                    </div>
                    <div class="col-2 fw-bold text-truncate py-3 bg-white border-top">
                        Last Name
                    </div>
                    <div class="col-2 fw-bold text-truncate py-3 bg-white border-top">
                        Email
                    </div>
                    <div class="col-2 fw-bold text-truncate py-3 bg-white border-top">
                        Modified
                    </div>
                    <div class="col-3 fw-bold text-truncate py-3 bg-white border-top border-right">
                        Created
                    </div>
                </div>';

                foreach($results as $user)
                {
                    $response .= '
                    <div 
                        class="row border-bottom-dashed admin-row pe-0" id="row_'. $user->getId() .'">
                        <div class="col-4 fw-bold d-block d-md-none text-truncate bg-white ps-4 py-3 bg-white">
                            #ID
                        </div>
                        <div class="col-8 col-md-1 text-truncate py-3 bg-white border-left">
                            #'. $this->encryptor->decrypt($user->getId()) .'
                        </div>
                        <div class="col-4 d-block d-md-none fw-bold text-truncate py-3 bg-white">
                            First Name
                        </div>
                        <div class="col-8 col-md-2 text-truncate py-3 bg-white">
                            '. $this->encryptor->decrypt($user->getFirstName()) .'
                        </div>
                        <div class="col-4 d-block d-md-none fw-bold text-truncate py-3 bg-white">
                            Last Name
                        </div>
                        <div class="col-8 col-md-2 text-truncate py-3 bg-white">
                            '. $this->encryptor->decrypt($user->getLastName()) .'
                        </div>
                        <div class="col-4 d-block d-md-none fw-bold text-truncate py-3 bg-white">
                            Email
                        </div>
                        <div class="col-8 col-md-2 text-truncate py-3 bg-white">
                            '. $this->encryptor->decrypt($user->getEmail()) .'
                        </div>
                        <div class="col-4 d-block d-md-none fw-bold text-truncate py-3 bg-white">
                            Modified
                        </div>
                        <div class="col-8 col-md-2 text-truncate py-3 bg-white">
                            '. $user->getModified()->format('Y-m-d H:i:s') .'
                        </div>
                        <div class="col-4 d-block d-md-none fw-bold text-truncate py-3 bg-white">
                            Created
                        </div>
                        <div class="col-8 col-md-2 text-truncate py-3 bg-white">
                            '. $user->getCreated()->format('Y-m-d') .'
                        </div>
                        <div class="col-12 col-md-1 text-truncate mt-3 mt-md-0 py-3 bg-white pe-0 border-right">
                            <a
                                href=""
                                class="float-start float-sm-end open-user-modal ms-5 ms-md-0 edit-link"
                                data-user-id="'. $user->getId() .'"
                                data-action="click->admin--users#onClickEditUser"
                            >
                                <i class="fa-solid fa-pen-to-square edit-icon"></i>
                            </a>
                            <a
                                href=""
                                class="delete-icon float-end open-delete-users-modal"
                                data-bs-toggle="modal"
                                data-users-id="'. $user->getId() .'"
                                data-bs-target="#modal_delete_users"
                            >
                                <i class="fa-solid fa-trash-can"></i>
                            </a>
                        </div>
                    </div>';
                }

                $response .= '
                <div class="row py-3">
                    <div class="col-12">
                        '. $pagination .'
                    </div>
                </div>
            </div>
        </section>
    
        <!-- Delete Users Modal -->
        <div class="modal fade" id="modal_delete_users" tabindex="-1" aria-labelledby="user_delete_label" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="user_delete_label">Delete Manufacturer</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-12 mb-0">
                                Are you sure you would like to delete this user? This action cannot be undone.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-bs-dismiss="modal">CANCEL</button>
                        <button type="submit" class="btn btn-danger btn-sm" id="delete_user">DELETE</button>
                    </div>
                </div>
            </div>
        </div>';

        return new JsonResponse($response);
    }

    #[Route('/admin/user/form/update', name: 'update_user')]
    public function getUpdateUser(Request $request): Response
    {
        $userId = (int) $request->request->get('user-id');
        $user = $this->em->getRepository(User::class)->find($userId);
        $response = '';

        if($user != null)
        {
            $firstName = $user->getFirstName();
            $lastName = $user->getLastName();
            $email = $user->getEmail();
            $roles = $user->getRoles();

            $response = $this->getUserForm($firstName, $lastName, $email, $roles, $userId);
        }

        return new JsonResponse($response);
    }

    #[ArrayShape(['plainPassword' => "string", 'hashedPassword' => "string"])]
    private function setUserPassword($userId)
    {
        // ... e.g. get the user data from a registration form
        $user = $this->em->getRepository(User::class)->find($userId);

        $plaintextPassword = $this->generatePassword();

        // hash the password (based on the security.yaml config for the $user class)
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $plaintextPassword
        );

        return [
            'plainPassword' => $plaintextPassword,
            'hashedPassword' => $hashedPassword
        ];
    }

    private function generatePassword(): string
    {
        $sets = [];
        $sets[] = 'abcdefghjkmnpqrstuvwxyz';
        $sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
        $sets[] = '23456789';
        $sets[] = '!@$%*?';

        $all = '';
        $password = '';

        foreach ($sets as $set)
        {
            $password .= $set[array_rand(str_split($set))];
            $all .= $set;
        }

        $all = str_split($all);

        for ($i = 0; $i < 16 - count($sets); $i++)
        {
            $password .= $all[array_rand($all)];
        }

        $this->plainPassword = str_shuffle($password);

        return $this->plainPassword;
    }

    private function getUserForm($firstName = '', $lastName = '', $email = '', $userRoles = [], $userId = 0)
    {
        $roles = [
            'admin' => 'ROLE_ADMIN',
            'staff' => 'ROLE_STAFF',
            'tasks' => 'ROLE_TASKS',
        ];
        $response = '
        <form name="users-form" method="post" data-action="submit->admin--users#onSubmitUsersForm">
            <input type="hidden" name="user-id" id="user_id" value="'. $userId .'">
            <input type="hidden" name="reset-password" id="reset_password" value="false">
            <section class="content-header border-bottom px-4 py-4 admin-header">
                <div class="row w-100">
                    <div class="col-12 col-md-6">
                        <h4 class="text-truncate">
                                New User
                        </h4>
                    </div>
                    <div class="col-12 col-md-6 text-end">
                        <button 
                            class="btn btn-primary w-sm-100 mb-3 mb-sm-0 me-0 me-md-1 text-truncate" 
                            type="button" 
                            name="go-back" 
                            id="go_back"
                            data-action="click->admin--users#onClickBackBtn"
                        >
                            <span class="btn-label">
                                <i class="action-icon far fa-chevron-double-left"></i>
                            </span>
                        </button>
                        <button 
                            class="action-saveAndContinue btn btn-secondary text-truncate action-save w-sm-100 mb-3 mb-md-0 me-0 me-md-1" 
                            type="submit" 
                            name="save_continue"
                            data-action="submit->admin--users#onSubmitUsersForm"
                        >
                            <span 
                                class="btn-label"
                            >
                                <i class="action-icon far fa-edit"></i>
                                Save
                            </span>
                        </button>
    
                        <button 
                            class="action-saveAndReturn btn btn-primary text-truncate action-save w-sm-100 mb-3 mb-md-0 me-0 me-md-1" 
                            type="submit" 
                            name="save_return"
                            data-action="submit->admin--users#onSubmitUsersForm"
                        >
                            <span class="btn-label">Save and exit</span>
                        </button>
    
                        <button 
                            class="action-saveAndReturn btn btn-warning text-truncate action-save w-sm-100" 
                            type="submit" 
                            name="save_reset_password" 
                            id="save_reset_password"
                        >
                        <span class="btn-label">
                            <i class="fa-solid fa-paper-plane me-2"></i>
                            Reset Password
                        </span>
                        </button>
                    </div>
                </div>
            </section>
            
            <section class="px-4">
                <div class="row mt-4">
                    <div class="col-12 col-md-6">
                        <label class="ms-2 text-primary">
                            First Name <span class="text-danger">*</span>
                        </label>
                        <input 
                            name="first-name" 
                            id="first_name" 
                            class="form-control" 
                            type="text" 
                            value="'. $this->encryptor->decrypt($firstName) .'" 
                            placeholder="First Name"
                        >
                        <div class="hidden_msg" id="error_first_name">
                            Required Field
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="ms-2 text-primary">
                            Last Name <span class="text-danger">*</span>
                        </label>
                        <input 
                            name="last-name" 
                            id="last_name" 
                            class="form-control" 
                            type="text" 
                            value="'. $this->encryptor->decrypt($lastName) .'" 
                            placeholder="Last Name"
                        >
                        <div class="hidden_msg" id="error_last_name">
                            Required Field
                        </div>
                    </div>
                </div>
    
                <div class="row mt-4">
                    <div class="col-12 col-md-6">
                        <label class="ms-2 text-primary">
                            Email <span class="text-danger">*</span>
                        </label>
                        <input 
                            name="email" 
                            id="email" 
                            class="form-control" 
                            type="text" 
                            value="'. $this->encryptor->decrypt($email) .'" 
                            placeholder="Email"
                        >
                        <div class="hidden_msg" id="error_email">
                            Required Field
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="text-primary">User Roles <span class="text-danger">*</span> </label>
                        <div class="position-relative">
                            <div 
                                class="form-control cursor-text text-placeholder" 
                                id="role"
                                data-action="click->admin--users#onClickRoleField"
                            >';

                            if(is_array($userRoles) && count($userRoles) > 0)
                            {
                                foreach($userRoles as $role)
                                {
                                    if($role != 'ROLE_USER')
                                    {
                                        $response .= '
                                        <span class="badge bg-disabled me-3 my-1" id="role_badge_'. $role .'">
                                            <span id="role_badge_string_'. $role .'">
                                                '. $role .'
                                            </span>
                                        </span>';
                                    }

                                }
                            }
                            else
                            {
                                $response .= 'Select a Role';
                            }

                            $response .= '
                            </div>
                            <div id="roles_list" class="row" style="display: none">';

                                if(is_array($userRoles) && count($userRoles) > 0)
                                {
                                    foreach ($userRoles as $role)
                                    {
                                        $response .= '
                                        <input 
                                            type="hidden" 
                                            name="roles[]" 
                                            class="role_hidden" 
                                            data-name="'. $role .'"
                                            id="role_hidden_field_'. $role .'" 
                                            value="'. $role .'" 
                                        >';
                                    }
                                }

                                $response .= '
                                <div id="role_list_container">
                                    <div class="px-3 row">
                                        <div class="bg-dropdown px-0 col-12">';

                                            foreach($roles as $role)
                                            {
                                                $response .= '
                                                <div class="row">
                                                    <div class="col-12 edit-role d-table" data-role-id="'. $role .'">
                                                        <div 
                                                            class="row role-row d-table-row" data-role-id="'. $role .'"
                                                            data-action="mouseover->admin--users#onMouseOverRole mouseout->admin--users#onMouseOutRole"
                                                            >
                                                            <div 
                                                                class="col-10 py-2 d-table-cell align-middle role-select" 
                                                                data-role-id="'. $role .'" 
                                                                data-role="'. $role .'" 
                                                                id="role_row_id_'. $role .'"
                                                                data-action="click->admin--users#onClickRoleSelect"
                                                            >
                                                                <span id="role_string_'. $role .'">
                                                                    '. $role .'
                                                                </span>
                                                                <input 
                                                                    type="text" 
                                                                    class="form-control form-control-sm role-form-ctrl" 
                                                                    value="'. $role .'" 
                                                                    data-role-field-role_user="" 
                                                                    id="role_edit_field_'. $role .'" 
                                                                    style="display: none"
                                                                >
                                                                <div class="hidden_msg" id="error_role_'. $role .'">
                                                                    Required Field
                                                                </div>
                                                            </div>
                                                            <div class="col-2 py-2 d-table-cell align-middle">
                                                               <a 
                                                                    href="" 
                                                                    class="float-end role-remove-icon me-3" 
                                                                    id="role_remove_'. $role .'" 
                                                                    data-role-id="'. $role .'" 
                                                                    style="display: none"
                                                                    data-action="click->admin--users#onClickRemoveRole"
                                                                >
                                                                   <i class="fa-solid fa-circle-minus"></i>
                                                               </a>
                                                               <a 
                                                                    href="" 
                                                                    class="float-end role-cancel-icon me-3" 
                                                                    id="role_cancel_'. $role .'" 
                                                                    data-role-cancel-id="'. $role .'" 
                                                                    style="display: none"
                                                                >
                                                                   <i class="fa-solid fa-xmark"></i>
                                                               </a>
                                                               <a 
                                                                    href="" 
                                                                    class="float-end role-save-icon me-3" 
                                                                    id="role_save_'. $role .'" 
                                                                    data-role-id="'. $role .'" 
                                                                    style="display: none"
                                                                >
                                                                   <i class="fa-solid fa-floppy-disk"></i>
                                                               </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>';
                                            }

                                        $response .= '
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="hidden_msg" id="error_roles">
                                Required Field
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </form>';

        return $response;
    }

    public function getPagination($pageId, $results, $url, $dataAction = '', $itemsPerPage = 10): string
    {
        $currentPage = $pageId;
        $totalPages = ceil(count($results) / $itemsPerPage);
        $limit = 5;
        $lastPage = $this->pageManager->lastPage($results);
        $pagination = '';

        if(count($results) > 0)
        {
            $pagination .= '
            <!-- Pagination -->
            <div class="row mt-3">
                <div class="col-12">';

            if ($lastPage > 1) {

                $previousPageNo = $currentPage - 1;
                $previousPage = $url . $previousPageNo;

                $pagination .= '
                <nav class="custom-pagination">
                    <ul class="pagination justify-content-center">
                ';

                $disabled = 'disabled';
                $dataDisabled = 'true';

                // Previous Link
                if ($currentPage > 1) {

                    $disabled = '';
                    $dataDisabled = 'false';
                }

                if ($totalPages >= 1 && $pageId <= $totalPages && $currentPage != 1)
                {
                    $pagination .= '
                    <li class="page-item ' . $disabled . '">
                        <a 
                            class="address-pagination" 
                            aria-disabled="' . $dataDisabled . '" 
                            data-page-id="' . $currentPage - 1 . '" 
                            href="' . $previousPage . '"
                            ' . $dataAction . '
                        >
                            <span aria-hidden="true">&laquo;</span> <span class="d-none d-sm-inline">Previous</span>
                        </a>
                    </li>
                    <li class="page-item ">
                        <a class="address-pagination" data-page-id="12" href="'. $url.'1">First</a>
                    </li>';
                }

                $i = max(1, $currentPage - $limit);
                $forLimit = min($currentPage + $limit, $totalPages);
                $isActive = false;

                for (; $i <= $forLimit; $i++)
                {
                    $active = '';

                    if ($i == (int)$currentPage) {

                        $active = 'active';
                        $isActive = true;
                    }

                    // Go to previous page if all records for a page have been deleted
                    if (!$isActive && $i == count($results)) {

                        $active = 'active';
                    }

                    $pagination .= '
                    <li class="page-item ' . $active . '">
                        <a 
                            class="address-pagination" 
                            data-page-id="' . $i . '" 
                            href="' . $url . $i . '"
                            ' . $dataAction . '
                        >' . $i . '</a>
                    </li>';
                }

                $disabled = 'disabled';
                $dataDisabled = 'true';

                if ($currentPage < $lastPage) {

                    $disabled = '';
                    $dataDisabled = 'false';
                }

                if ($currentPage < $lastPage)
                {
                    $pagination .= '
                    <li class="page-item ">
                        <a class="address-pagination" data-page-id="12" href="'. $url . $lastPage .'">Last</a>
                    </li>
                    <li class="page-item ' . $disabled . '">
                        <a 
                            class="address-pagination"  
                            aria-disabled="' . $dataDisabled . '" 
                            data-page-id="' . $currentPage + 1 . '" 
                            href="' . $url . $currentPage + 1 . '"
                            '. $dataAction .'
                        >
                            <span class="d-none d-sm-inline">Next</span> <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>';
                }

                $pagination .= '
                        </ul>
                    </nav>
                    <input type="hidden" id="page_no" value="' . $currentPage . '">
                </div>';
            }
        }

        return $pagination;
    }
}
