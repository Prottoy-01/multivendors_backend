<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;

class ProductAttributeSeeder extends Seeder
{
    public function run(): void
    {
        // Color Attribute
        $colorAttr = ProductAttribute::create([
            'name' => 'color',
            'display_name' => 'Color',
            'type' => 'color',
            'is_filterable' => true,
        ]);

        $colors = [
            ['value' => 'red', 'color_code' => '#FF0000'],
            ['value' => 'blue', 'color_code' => '#0000FF'],
            ['value' => 'green', 'color_code' => '#00FF00'],
            ['value' => 'black', 'color_code' => '#000000'],
            ['value' => 'white', 'color_code' => '#FFFFFF'],
            ['value' => 'yellow', 'color_code' => '#FFFF00'],
            ['value' => 'pink', 'color_code' => '#FFC0CB'],
            ['value' => 'purple', 'color_code' => '#800080'],
            ['value' => 'orange', 'color_code' => '#FFA500'],
            ['value' => 'gray', 'color_code' => '#808080'],
        ];

        foreach ($colors as $color) {
            ProductAttributeValue::create([
                'attribute_id' => $colorAttr->id,
                'value' => $color['value'],
                'color_code' => $color['color_code'],
            ]);
        }

        // Size Attribute
        $sizeAttr = ProductAttribute::create([
            'name' => 'size',
            'display_name' => 'Size',
            'type' => 'select',
            'is_filterable' => true,
        ]);

        $sizes = ['xs', 's', 'm', 'l', 'xl', 'xxl', 'xxxl'];
        foreach ($sizes as $size) {
            ProductAttributeValue::create([
                'attribute_id' => $sizeAttr->id,
                'value' => $size,
            ]);
        }

        // Material Attribute
        $materialAttr = ProductAttribute::create([
            'name' => 'material',
            'display_name' => 'Material',
            'type' => 'select',
            'is_filterable' => true,
        ]);

        $materials = ['cotton', 'polyester', 'leather', 'silk', 'wool', 'denim'];
        foreach ($materials as $material) {
            ProductAttributeValue::create([
                'attribute_id' => $materialAttr->id,
                'value' => $material,
            ]);
        }
    }
}