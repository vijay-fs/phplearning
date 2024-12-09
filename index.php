<?php
require 'vendor/autoload.php';

use MongoDB\Client;
use MongoDB\BSON\ObjectId;

/**
 * Simple Router:
 * Parse the URI and HTTP method, then call the appropriate handler.
 */

header('Content-Type: application/json');

// Setup MongoDB client
$client = new Client("mongodb://localhost:27017");
$db = $client->mydb;
$collection = $db->products;

// Parse the incoming request
$method = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];

// Remove query string, split path
$path = parse_url($requestUri, PHP_URL_PATH);
$pathParts = array_values(array_filter(explode('/', $path)));
echo "Path: $requestUri; Method: $method; PathParts: " . print_r($pathParts, true) . "\n";
// We expect URLs like:
// GET /products
// GET /products/{id}
// POST /products
// PUT /products/{id}
// DELETE /products/{id}
$resource = $pathParts[0] ?? null;
$id = $pathParts[1] ?? null;

// A helper function to read JSON body
function getJsonInput() {
    $input = file_get_contents('php://input');
    return json_decode($input, true);
}

// A helper to send JSON response
function sendJson($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

// A helper function to transform a single MongoDB document by moving _id -> id
function transformDocument($doc) {
    if ($doc === null) return null;

    $arr = (array)$doc;

    $id = isset($arr['_id']) && $arr['_id'] instanceof ObjectId ? (string)$arr['_id'] : null;
    unset($arr['_id']);

    // Prepend the 'id' key, and keep the rest of the fields dynamic
    $newArr = ['id' => $id] + $arr;

    return $newArr;
}


// A helper function to transform an array of MongoDB documents
function transformDocuments($docs) {
    $results = [];
    foreach ($docs as $doc) {
        $results[] = transformDocument($doc);
    }
    return $results;
}

// Check if resource matches 'products'
if ($resource !== 'products') {
    sendJson(['error' => 'Not Found'], 404);
}

// Handle requests based on method and presence of ID
switch ($method) {
    case 'GET':
        if ($id) {
            // GET /products/{id} - Retrieve single product
            try {
                $product = $collection->findOne(['_id' => new ObjectId($id)]);
                if ($product) {
                    $product = transformDocument($product);
                    sendJson($product);
                } else {
                    sendJson(['error' => 'Product not found'], 404);
                }
            } catch (Exception $e) {
                sendJson(['error' => 'Invalid ID format'], 400);
            }
        } else {
            // GET /products - Retrieve all products
            $cursor = $collection->find();
            $products = iterator_to_array($cursor);
            $products = transformDocuments($products);
            sendJson($products);
        }
        break;
        
    case 'POST':
        // POST /products - Create a new product
        $data = getJsonInput();
        if (!$data || !isset($data['name']) || !isset($data['price'])) {
            sendJson(['error' => 'Invalid input: name and price required'], 400);
        }

        $insertResult = $collection->insertOne([
            'name' => $data['name'],
            'price' => (float)$data['price']
        ]);
        
        if ($insertResult->getInsertedCount() === 1) {
            $insertedId = (string)$insertResult->getInsertedId();
            sendJson(['message' => 'Product created', 'id' => $insertedId], 201);
        } else {
            sendJson(['error' => 'Failed to create product'], 500);
        }
        break;
        
    case 'PUT':
        // PUT /products/{id} - Update a product
        if (!$id) {
            sendJson(['error' => 'ID required'], 400);
        }
        $data = getJsonInput();
        if (!$data) {
            sendJson(['error' => 'No data provided'], 400);
        }

        // Build the update array
        $update = [];
        if (isset($data['name'])) {
            $update['name'] = $data['name'];
        }
        if (isset($data['price'])) {
            $update['price'] = (float)$data['price'];
        }

        if (empty($update)) {
            sendJson(['error' => 'No valid fields to update'], 400);
        }

        try {
            $updateResult = $collection->updateOne(
                ['_id' => new ObjectId($id)],
                ['$set' => $update]
            );
            
            if ($updateResult->getModifiedCount() === 1) {
                sendJson(['message' => 'Product updated']);
            } else {
                // Could be not found or no changes made
                $product = $collection->findOne(['_id' => new ObjectId($id)]);
                if (!$product) {
                    sendJson(['error' => 'Product not found'], 404);
                } else {
                    sendJson(['message' => 'No changes made'], 200);
                }
            }
        } catch (Exception $e) {
            sendJson(['error' => 'Invalid ID format'], 400);
        }
        break;
        
    case 'DELETE':
        // DELETE /products/{id} - Delete a product
        if (!$id) {
            sendJson(['error' => 'ID required'], 400);
        }
        try {
            $deleteResult = $collection->deleteOne(['_id' => new ObjectId($id)]);
            if ($deleteResult->getDeletedCount() === 1) {
                sendJson(['message' => 'Product deleted']);
            } else {
                sendJson(['error' => 'Product not found'], 404);
            }
        } catch (Exception $e) {
            sendJson(['error' => 'Invalid ID format'], 400);
        }
        break;
        
    default:
        sendJson(['error' => 'Method Not Allowed'], 405);
        break;
}
