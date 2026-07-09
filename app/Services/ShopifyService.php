<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ShopifyService
{
    protected string $store;
    protected string $token;
    protected string $version;

    public function __construct()
    {
        $this->store = config('services.shopify.store');
        $this->token = config('services.shopify.token');
        $this->version = config('services.shopify.version');
    }

    public function createProduct(array $product)
    {
        $url = "https://{$this->store}/admin/api/{$this->version}/products.json";

        return Http::withOptions([
                        'verify' => false
                    ])->withHeaders([
                        'X-Shopify-Access-Token' => $this->token,
                        'Content-Type' => 'application/json',
                    ])->post($url, [
                        'product' => [
                            'title' => $product['title'],
                            'body_html' => $product['body_html'],
                            'vendor' => $product['vendor'],
                            'product_type' => $product['product_type'],
                            'tags' => $product['tags'],

                            'variants' => [[
                                'sku' => $product['variant_sku'],
                                'price' => $product['variant_price'],
                                'compare_at_price' => $product['variant_compare_at_price'],
                                'inventory_management' => $product['variant_inventory_tracker'],
                                'inventory_policy' => $product['variant_inventory_policy'],
                                'inventory_quantity' => $product['variant_inventory_qty'],
                                'requires_shipping' => $product['variant_requires_shipping'],
                                'taxable' => $product['variant_taxable'],
                                'weight' => $product['variant_weight'],
                                'weight_unit' => strtolower($product['variant_weight_unit']),
                            ]],

                            'images' => !empty($product['image_src']) ? [
                                        [
                                            'src' => trim($product['image_src']),
                                            'alt' => $product['image_alt_text'] ?? null,
                                        ]
                                    ] : [],
                        ]
                    ]);
    }

    public function updateProduct(int $shopifyProductId, array $product)
    {
        // dd($shopifyProductId, $product);
        $url = "https://{$this->store}/admin/api/{$this->version}/products/{$shopifyProductId}.json";

        return Http::withOptions([
                        'verify' => false
                    ])->withHeaders([
                        'X-Shopify-Access-Token' => $this->token,
                        'Content-Type' => 'application/json',
                    ])->put($url, [
                        'product' => [
                            'id' => $shopifyProductId,
                            'title' => $product['title'],
                            'body_html' => $product['body_html'],
                            'vendor' => $product['vendor'],
                            'product_type' => $product['product_type'],
                            'tags' => $product['tags'],
                            'images' => [
                                        [
                                            'src' => $product['image_src'],
                                            'alt' => $product['image_alt_text'],
                                        ]
                                    ],     
                        ]
                    ]);
        
    }
}