<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Upload;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Services\ShopifyService;

class ProcessCsvJob implements ShouldQueue
{
    use Queueable;

    public Upload $upload;

    public $tries = 3;
    /**
     * Create a new job instance.
     */
    public function __construct(Upload $upload)
    {
        $this->upload = $upload;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {

            $this->upload->update([
                'status' => 'processing'
            ]);

            $path = Storage::path($this->upload->file_path);

            if (!file_exists($path)) {
                throw new \Exception('CSV file not found.');
            }

            $handle = fopen($path, 'r');

            if (!$handle) {
                throw new \Exception('Unable to open CSV file.');
            }

            $headers = fgetcsv($handle);
            if (!$headers) {
                throw new \Exception('Invalid CSV headers.');
            }

            $shopify = new ShopifyService();

            while (($row = fgetcsv($handle, 1000, ",")) !== false) {
                
                if (count($headers) != count($row)) {

                    Log::warning('CSV column mismatch', [
                        'row' => $row
                    ]);

                    continue;
                }

                $product = array_combine($headers, $row);

                if ($product === false) {
                    Log::warning('Invalid CSV row skipped.');
                    continue;
                }

                $save_product = Product::updateOrCreate(
                    [
                        'variant_sku' => $product['Variant SKU'] ?? null,
                    ],
                    [
                        'upload_id' => $this->upload->id,
                        'handle' => $product['Handle'] ?? null,
                        'title'     => $product['Title'] ?? null,
                        'body_html' => $product['Body HTML'] ?? null,
                        'vendor'    => $product['Vendor'] ?? null,
                        'product_type' => $product['Product Type'] ?? null,
                        'tags'      => $product['Tags'] ?? null,
                        'published' => isset($product['Published']) ? filter_var($product['Published'], FILTER_VALIDATE_BOOLEAN) : true,
                        'variant_price' => isset($product['Variant Price']) ? $product['Variant Price'] : null,
                        'variant_compare_at_price' => isset($product['Variant Compare At Price']) ? $product['Variant Compare At Price'] : null,
                        'variant_requires_shipping' => isset($product['Variant Requires Shipping']) ? filter_var($product['Variant Requires Shipping'], FILTER_VALIDATE_BOOLEAN) : true,
                        'variant_taxable' => isset($product['Variant Taxable']) ? filter_var($product['Variant Taxable'], FILTER_VALIDATE_BOOLEAN) : true,
                        'variant_inventory_tracker' => $product['Variant Inventory Tracker'] ?? null,
                        'variant_inventory_qty' => isset($product['Variant Inventory Qty']) ? (int)$product['Variant Inventory Qty'] : 0,
                        'variant_inventory_policy' => $product['Variant Inventory Policy'] ?? null,
                        'variant_fulfillment_service' => $product['Variant Fulfillment Service'] ?? null,
                        'variant_weight' => isset($product['Variant Weight']) ? $product['Variant Weight'] : null,
                        'variant_weight_unit' => $product['Variant Weight Unit'] ?? null,
                        'image_src' => $product['Image Src'] ?? null,
                        'image_position' => isset($product['Image Position']) ? $product['Image Position'] : null,
                        'image_alt_text' => $product['Image Alt Text'] ?? null
                    ]
                );

                if (!$save_product) {
                    Log::warning('Unable to save product.');
                    continue;
                }

                $save_product->update(['status' => 2]);
                try {
                    if ($save_product->shopify_product_id) {
                        $payload=[
                            'title' => $product['Title'] ?? null,
                            'body_html' => $product['Body HTML'] ?? null,
                            'vendor' => $product['Vendor'] ?? null,
                            'product_type' => $product['Product Type'] ?? null,
                            'tags' => $product['Tags'] ?? null,
                            'image_src' => $product['Image Src'] ?? null,
                            'image_alt_text' => $product['Image Alt Text'] ?? null,
                        ];
                        $response = $shopify->updateProduct(
                                $save_product->shopify_product_id,
                                $payload
                            );
                            
                        if ($response->failed()) {
                           
                            $save_product->update([
                                'status' => 4,
                                'error_message' => $response->body(),
                            ]);
                            Log::error('Shopify API error: ' . $response->body());
                        } else {
                            $shopify_product = $response->json();
                            $save_product->update([
                                'status' => 3,
                                'error_message' => null,
                            ]);
                        }   
                    }
                    else {
                        $response = $shopify->createProduct([
                            'title' => $product['Title'] ?? null,
                            'body_html' => $product['Body HTML'] ?? null,
                            'vendor' => $product['Vendor'] ?? null,
                            'product_type' => $product['Product Type'] ?? null,
                            'tags' => $product['Tags'] ?? null,

                            'variant_sku' => $product['Variant SKU'] ?? null,
                            'variant_price' => $product['Variant Price'] ?? null,
                            'variant_compare_at_price' => $product['Variant Compare At Price'] ?? null,
                            'variant_inventory_tracker' => $product['Variant Inventory Tracker'] ?? null,
                            'variant_inventory_qty' => (int)($product['Variant Inventory Qty'] ?? 0),
                            'variant_inventory_policy' => $product['Variant Inventory Policy'] ?? null,
                            'variant_requires_shipping' => filter_var($product['Variant Requires Shipping'] ?? true, FILTER_VALIDATE_BOOLEAN),
                            'variant_taxable' => filter_var($product['Variant Taxable'] ?? true, FILTER_VALIDATE_BOOLEAN),
                            'variant_weight' => $product['Variant Weight'] ?? null,
                            'variant_weight_unit' => $product['Variant Weight Unit'] ?? 'kg',

                            'image_src' => $product['Image Src'] ?? null,
                            'image_alt_text' => $product['Image Alt Text'] ?? null,
                        ]);
                        
                        if ($response->failed()) {
                            
                            $save_product->update([
                                'status' => 4,
                                'error_message' => $response->body(),
                            ]);
                            Log::error('Shopify API error: ' . $response->body());
                        } else {
                            $shopify_product = $response->json();
                            $save_product->update([
                                'status' => 3,
                                'shopify_product_id' => $shopify_product['product']['id'] ?? null,
                                'error_message' => null,
                            ]);
                        }   
                    }
                } catch (\Exception $e) {
                    $save_product->update([
                        'status' => 4,
                        'error_message' => $e->getMessage()
                    ]);
                    Log::error('Shopify API exception: ' . $e->getMessage());
                    // throw $e;
                }
            }

            $this->upload->update([
                'status' => 'completed'
            ]);

            fclose($handle);

        } catch (\Exception $e) {

            $this->upload->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);
            Log::error($e->getMessage());
            // throw $e;
        }
    }
}
