<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class ProductController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->call->library('session');
        $this->call->database();
        $this->call->model('ProductModel');
    }

    private function product_data()
    {
        return [
            'product_name' => trim((string) ($_POST['product_name'] ?? '')),
            'description' => trim((string) ($_POST['description'] ?? '')),
            'price' => trim((string) ($_POST['price'] ?? '')),
            'quantity' => trim((string) ($_POST['quantity'] ?? '')),
        ];
    }

    private function validation_errors(array $data)
    {
        $errors = [];

        if ($data['product_name'] === '') {
            $errors[] = 'Product name is required.';
        }
        if ($data['description'] === '') {
            $errors[] = 'Description is required.';
        }
        if ($data['price'] === '' || !is_numeric($data['price']) || (float) $data['price'] < 0) {
            $errors[] = 'Price must be a non-negative number.';
        }
        if ($data['quantity'] === '' || filter_var($data['quantity'], FILTER_VALIDATE_INT) === false || (int) $data['quantity'] < 0) {
            $errors[] = 'Quantity must be a non-negative whole number.';
        }

        return $errors;
    }

    private function render_form($product, $errors = [], $editing = false)
    {
        $this->call->view('products/form', [
            'title' => $editing ? 'Edit Product' : 'Add Product',
            'product' => $product,
            'errors' => $errors,
            'editing' => $editing,
            'form_action' => $editing
                ? site_url('products/edit/' . (int) $product['id'])
                : site_url('products/create'),
        ]);
    }

    public function index()
    {
        $this->call->view('products/index', [
            'title' => 'Product Management',
            'products' => $this->ProductModel->all(),
            'notice' => $this->session->flashdata('product_notice'),
            'error' => $this->session->flashdata('product_error'),
            'username' => $this->session->userdata('authenticated_user'),
        ]);
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->render_form([
                'product_name' => '',
                'description' => '',
                'price' => '',
                'quantity' => '',
            ]);
            return;
        }

        $data = $this->product_data();
        $errors = $this->validation_errors($data);
        if ($errors) {
            $this->render_form($data, $errors);
            return;
        }

        $this->ProductModel->insert([
            'product_name' => $data['product_name'],
            'description' => $data['description'],
            'price' => number_format((float) $data['price'], 2, '.', ''),
            'quantity' => (int) $data['quantity'],
        ]);
        $this->session->set_flashdata('product_notice', 'Product added successfully.');
        redirect('products', false, false);
    }

    public function edit($id)
    {
        $id = (int) $id;
        $product = $this->ProductModel->find($id);
        if (!$product) {
            show_404();
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->render_form($product, [], true);
            return;
        }

        $data = $this->product_data();
        $errors = $this->validation_errors($data);
        if ($errors) {
            $data['id'] = $id;
            $this->render_form($data, $errors, true);
            return;
        }

        $this->ProductModel->update($id, [
            'product_name' => $data['product_name'],
            'description' => $data['description'],
            'price' => number_format((float) $data['price'], 2, '.', ''),
            'quantity' => (int) $data['quantity'],
        ]);
        $this->session->set_flashdata('product_notice', 'Product updated successfully.');
        redirect('products', false, false);
    }

    public function delete($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->ProductModel->delete((int) $id);
            $this->session->set_flashdata('product_notice', 'Product deleted successfully.');
        }

        redirect('products', false, false);
    }
}
