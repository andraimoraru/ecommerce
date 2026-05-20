<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\Address;
use App\Models\User;

final class Customers extends Controller
{
    // Render the admin customer list.
    public function index(): void
    {
        $perPage = 25;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * $perPage;
        $userModel = new User();

        $this->render('admin/customers/index', [
            'title' => 'Customers',
            'customers' => $userModel->allCustomersAdmin($perPage, $offset),
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total_items' => $userModel->countCustomersAdmin(),
                'total_pages' => max(1, (int)ceil($userModel->countCustomersAdmin() / $perPage)),
            ],
        ], 'admin');
    }

    // Render one customer's detail page with addresses and orders.
    public function show(array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $userModel = new User();
        $customer = $userModel->findCustomerById($id);

        if (!$customer) {
            http_response_code(404);
            echo 'Customer not found';
            return;
        }

        $this->render('admin/customers/show', [
            'title' => trim((string)$customer['first_name'] . ' ' . (string)$customer['last_name']),
            'customer' => $customer,
            'orders' => $userModel->ordersForCustomer($id),
            'addresses' => (new Address())->allForUser($id),
            'delete_error' => (string)($_SESSION['admin_customer_delete_error'] ?? ''),
        ], 'admin');

        unset($_SESSION['admin_customer_delete_error']);
    }

    // Render the customer edit form.
    public function editForm(array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $customer = (new User())->findCustomerById($id);

        if (!$customer) {
            http_response_code(404);
            echo 'Customer not found';
            return;
        }

        $this->render('admin/customers/edit', [
            'title' => 'Edit Customer',
            'customer' => $customer,
            'errors' => [],
        ], 'admin');
    }

    // Validate and persist customer changes.
    public function update(array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $userModel = new User();
        $customer = $userModel->findCustomerById($id);

        if (!$customer) {
            http_response_code(404);
            echo 'Customer not found';
            return;
        }

        $data = [
            'email' => trim((string)($_POST['email'] ?? '')),
            'first_name' => trim((string)($_POST['first_name'] ?? '')),
            'last_name' => trim((string)($_POST['last_name'] ?? '')),
            'phone' => trim((string)($_POST['phone'] ?? '')),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ];

        $errors = [];
        if ($data['first_name'] === '') {
            $errors['first_name'] = 'Required.';
        }
        if ($data['last_name'] === '') {
            $errors['last_name'] = 'Required.';
        }
        if ($data['email'] === '') {
            $errors['email'] = 'Required.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email address.';
        } elseif ($userModel->emailExistsForAnotherUser($data['email'], $id)) {
            $errors['email'] = 'Email address already exists.';
        }

        if ($errors) {
            $this->render('admin/customers/edit', [
                'title' => 'Edit Customer',
                'customer' => array_merge($customer, $data),
                'errors' => $errors,
            ], 'admin');
            return;
        }

        $userModel->updateCustomer($id, $data);

        header('Location: ' . URLROOT . '/admin/customers/' . $id);
        exit;
    }

    // Delete a customer only when no order history exists.
    public function delete(array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $userModel = new User();
        $customer = $userModel->findCustomerById($id);

        if (!$customer) {
            http_response_code(404);
            echo 'Customer not found';
            return;
        }

        if ($userModel->customerHasOrders($id)) {
            $_SESSION['admin_customer_delete_error'] = 'Customers with orders cannot be deleted.';
            header('Location: ' . URLROOT . '/admin/customers/' . $id);
            exit;
        }

        $userModel->deleteCustomer($id);

        header('Location: ' . URLROOT . '/admin/customers');
        exit;
    }
}
