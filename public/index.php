<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/views/layouts/header.php';
require_once APP_ROOT . '/app/controllers/ContractsController.php';
require_once APP_ROOT . '/app/controllers/CompaniesController.php';
require_once APP_ROOT . '/app/controllers/PeopleController.php';
require_once APP_ROOT . '/app/controllers/ContractTypesController.php';
require_once APP_ROOT . '/app/controllers/AdminSettingsController.php';

$companiesController = new CompaniesController();
$PeopleController = new PeopleController();
$ContractsController = new ContractsController();
$ContractTypesController = new ContractTypesController();
$AdminSettingsController = new AdminSettingsController();
$page = $_GET['page'] ?? 'home';

switch ($page) {

        case 'contract_documents_create':
            $contractId = (int)($_GET['contract_id'] ?? 0);
            require APP_ROOT . '/app/views/contract_documents/create.php';
            break;

        case 'contract_documents_store':
            $ContractsController->storeDocument();
            break;
    case 'contracts':
        $ContractsController->index();
        break;

    case 'contracts_show':
        $ContractsController->show((int)($_GET['contract_id'] ?? 0));
        break;

    case 'contracts_create':
        $ContractsController->create();
        break;

    case 'contracts_store':
        $ContractsController->store();
        break;

    case 'contracts_edit':
        
        require_once APP_ROOT . '/app/controllers/ContractsController.php';
        (new ContractsController())->edit((int)($_GET['contract_id'] ?? 0));
    break;
       

    case 'contracts_update':
        $ContractsController->update((int)($_GET['contract_id'] ?? 0));
        break;

    case 'contracts_delete':
        $ContractsController->destroy((int)($_GET['contract_id'] ?? 0));
        break;

    case 'contracts_search':
        $ContractsController->search();
        break;

    case 'contracts_generate_print':
        $ContractsController->generateAndPrint((int)($_GET['contract_id'] ?? 0));
        break;

    case 'contracts_generate_html':
        $ContractsController->generateHtmlDocument((int)($_GET['contract_id'] ?? 0));
        break;

    case 'contracts_generate_word':
        $ContractsController->generateWordDocument((int)($_GET['contract_id'] ?? 0));
        break;

    case 'contract_types':
        $ContractTypesController->index();
        break;

    case 'contract_types_edit':
        $ContractTypesController->edit((int)($_GET['contract_type_id'] ?? 0));
        break;

    case 'contract_types_update':
        $ContractTypesController->update((int)($_GET['contract_type_id'] ?? 0));
        break;

case 'companies':
    $companiesController->index();
    break;

case 'companies_create':
    $companiesController->create();
    break;

case 'companies_store':
    $companiesController->store();
    break;

case 'companies_edit':
    $companiesController->edit((int)($_GET['company_id'] ?? 0));
    break;


case 'companies_update':
    $companiesController->update((int)($_GET['company_id'] ?? 0));
    break;

case 'companies_delete':
    $companiesController->destroy((int)($_GET['company_id'] ?? 0));
    break;

case 'companies_link_person':
    $companiesController->linkPerson((int)($_GET['company_id'] ?? 0));
    break;

case 'companies_unlink_person':
    $companiesController->unlinkPerson((int)($_GET['company_id'] ?? 0));
    break;

   case 'people':
    require_once APP_ROOT . '/app/controllers/PeopleController.php';
    (new PeopleController())->index();
    break;

case 'people_create':
    require_once APP_ROOT . '/app/controllers/PeopleController.php';
    (new PeopleController())->create();
    break;

case 'people_store':
    require_once APP_ROOT . '/app/controllers/PeopleController.php';
    (new PeopleController())->store();
    break;

case 'people_edit':
    require_once APP_ROOT . '/app/controllers/PeopleController.php';
    (new PeopleController())->edit();
    break;

case 'people_update':
    require_once APP_ROOT . '/app/controllers/PeopleController.php';
    (new PeopleController())->update();
    break;

case 'departments':
    require_once APP_ROOT . '/app/controllers/DepartmentsController.php';
    (new DepartmentsController())->index();
    break;

case 'department_edit':
    require_once APP_ROOT . '/app/controllers/DepartmentsController.php';
    (new DepartmentsController())->edit();
    break;

case 'department_update':
    require_once APP_ROOT . '/app/controllers/DepartmentsController.php';
    (new DepartmentsController())->update();
    break;
case 'departments_create':
    require_once APP_ROOT . '/app/controllers/DepartmentsController.php';
    (new DepartmentsController())->create();
    break;

case 'departments_store':
    require_once APP_ROOT . '/app/controllers/DepartmentsController.php';
    (new DepartmentsController())->store();
    break;


    case 'contract_document_email':
        require_once __DIR__ . '/contract_document_email.php';
        break;

    case 'contract_document_delete':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ContractsController->deleteDocument();
        } else {
            http_response_code(405);
            echo 'Method not allowed.';
        }
        break;

    case 'admin_settings':
        require_once APP_ROOT . '/app/controllers/AdminSettingsController.php';
        (new AdminSettingsController())->index();
        break;

    case 'admin_settings_update':
        require_once APP_ROOT . '/app/controllers/AdminSettingsController.php';
        (new AdminSettingsController())->update();
        break;

    default:
          $ContractsController->index();
       
        break;
}