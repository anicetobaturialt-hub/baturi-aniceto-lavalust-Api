<?php

defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

/**
 * ProductController
 *
 * CRUD for the `products` table. Every endpoint requires a valid
 * JWT access token (Authorization: Bearer <token>).
 */
class ProductController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->call->database();
        $this->call->library('api');
    }

    /**
     * GET /api/products
     */
    public function index()
    {
        $this->api->require_method('GET');
        $auth = $this->api->require_jwt();
        $this->api->rate_limit('products_' . $auth['sub']);

        $products = $this->db->table('products')
                             ->order_by('id', 'DESC')
                             ->get_all();

        $products = array_map([$this, 'format'], $products ?: []);

        $this->api->respond(['data' => $products, 'count' => count($products)]);
    }

    /**
     * GET /api/products/{id}
     */
    public function show($id)
    {
        $this->api->require_method('GET');
        $this->api->require_jwt();

        $product = $this->find($id);
        if (!$product) {
            $this->api->respond_error('Product not found', 404);
        }

        $this->api->respond(['data' => $this->format($product)]);
    }

    /**
     * POST /api/products
     */
    public function store()
    {
        $this->api->require_method('POST');
        $auth = $this->api->require_jwt();
        $this->api->rate_limit('products_' . $auth['sub']);

        $input  = $this->api->body();
        $errors = $this->validate($input, true);
        if ($errors) {
            $this->api->respond(['error' => 'Validation failed', 'status' => 422, 'errors' => $errors], 422);
        }

        $this->db->table('products')->insert([
            'product_name' => $input['product_name'],
            'description'  => $input['description'] ?? '',
            'price'        => number_format((float) $input['price'], 2, '.', ''),
            'quantity'     => (int) $input['quantity'],
        ]);

        $product = $this->find($this->db->last_id());

        $this->api->respond([
            'message' => 'Product created',
            'data'    => $this->format($product),
        ], 201);
    }

    /**
     * PUT|PATCH /api/products/{id}
     * PUT expects every field, PATCH accepts only the fields to change.
     */
    public function update($id)
    {
        if (!in_array($_SERVER['REQUEST_METHOD'], ['PUT', 'PATCH'], true)) {
            $this->api->respond_error('Method Not Allowed', 405);
        }

        $auth = $this->api->require_jwt();
        $this->api->rate_limit('products_' . $auth['sub']);

        $product = $this->find($id);
        if (!$product) {
            $this->api->respond_error('Product not found', 404);
        }

        $input  = $this->api->body();
        $errors = $this->validate($input, $_SERVER['REQUEST_METHOD'] === 'PUT');
        if ($errors) {
            $this->api->respond(['error' => 'Validation failed', 'status' => 422, 'errors' => $errors], 422);
        }

        $fields = [];
        if (isset($input['product_name'])) $fields['product_name'] = $input['product_name'];
        if (isset($input['description']))  $fields['description']  = $input['description'];
        if (isset($input['price']))        $fields['price']        = number_format((float) $input['price'], 2, '.', '');
        if (isset($input['quantity']))     $fields['quantity']     = (int) $input['quantity'];

        if (!$fields) {
            $this->api->respond_error('Nothing to update.', 422);
        }

        $this->db->table('products')->where('id', (int) $id)->update($fields);

        $this->api->respond([
            'message' => 'Product updated',
            'data'    => $this->format($this->find($id)),
        ]);
    }

    /**
     * DELETE /api/products/{id}
     */
    public function destroy($id)
    {
        $this->api->require_method('DELETE');
        $auth = $this->api->require_jwt();
        $this->api->rate_limit('products_' . $auth['sub']);

        if (!$this->find($id)) {
            $this->api->respond_error('Product not found', 404);
        }

        $this->db->table('products')->where('id', (int) $id)->delete();

        $this->api->respond(['message' => 'Product deleted']);
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private function find($id)
    {
        return $this->db->table('products')->where('id', (int) $id)->get() ?: null;
    }

    /**
     * Api::body() HTML-escapes input before it is stored. Decode it on the way
     * out so names like "Men's Shirt" are not shown as "Men&#039;s Shirt"
     * (clients must still escape output, which React/Vue do by default).
     */
    private function format($row)
    {
        return [
            'id'           => (int) $row['id'],
            'product_name' => html_entity_decode($row['product_name'], ENT_QUOTES, 'UTF-8'),
            'description'  => html_entity_decode((string) $row['description'], ENT_QUOTES, 'UTF-8'),
            'price'        => (float) $row['price'],
            'quantity'     => (int) $row['quantity'],
            'created_at'   => $row['created_at'],
        ];
    }

    private function validate(array $input, $require_all)
    {
        $errors = [];

        if ($require_all || array_key_exists('product_name', $input)) {
            $name = html_entity_decode((string) ($input['product_name'] ?? ''), ENT_QUOTES, 'UTF-8');
            if ($name === '' || mb_strlen($name) > 100) {
                $errors['product_name'] = 'Product name is required (max 100 characters).';
            }
        }

        if ($require_all || array_key_exists('price', $input)) {
            if (!isset($input['price']) || !is_numeric($input['price']) || $input['price'] < 0 || $input['price'] > 99999999.99) {
                $errors['price'] = 'Price must be a number between 0 and 99,999,999.99.';
            }
        }

        if ($require_all || array_key_exists('quantity', $input)) {
            if (!isset($input['quantity']) || filter_var($input['quantity'], FILTER_VALIDATE_INT) === false || $input['quantity'] < 0) {
                $errors['quantity'] = 'Quantity must be a whole number, 0 or more.';
            }
        }

        return $errors;
    }
}
