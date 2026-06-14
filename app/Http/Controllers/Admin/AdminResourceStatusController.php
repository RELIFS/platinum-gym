<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassSchedule;
use App\Models\Gallery;
use App\Models\GymClass;
use App\Models\Member;
use App\Models\Package;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Promo;
use App\Models\Testimonial;
use App\Models\Trainer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminResourceStatusController extends Controller
{
    /**
     * @var array<string, array{model: class-string<Model>, field: string, permission: string, active?: string, inactive?: string}>
     */
    private array $resources = [
        'members' => ['model' => Member::class, 'field' => 'status', 'permission' => 'manage_members', 'active' => 'active', 'inactive' => 'inactive'],
        'packages' => ['model' => Package::class, 'field' => 'is_active', 'permission' => 'manage_packages'],
        'classes' => ['model' => GymClass::class, 'field' => 'is_active', 'permission' => 'manage_classes'],
        'class-schedules' => ['model' => ClassSchedule::class, 'field' => 'is_active', 'permission' => 'manage_classes'],
        'products' => ['model' => Product::class, 'field' => 'is_active', 'permission' => 'manage_products'],
        'product-categories' => ['model' => ProductCategory::class, 'field' => 'is_active', 'permission' => 'manage_products'],
        'gallery' => ['model' => Gallery::class, 'field' => 'is_published', 'permission' => 'manage_content'],
        'testimonials' => ['model' => Testimonial::class, 'field' => 'is_published', 'permission' => 'manage_content'],
        'promos' => ['model' => Promo::class, 'field' => 'is_published', 'permission' => 'manage_content'],
        'trainers' => ['model' => Trainer::class, 'field' => 'is_active', 'permission' => 'manage_trainers'],
    ];

    public function toggle(Request $request, string $resource, int $id): RedirectResponse
    {
        abort_unless(isset($this->resources[$resource]), 404);

        $definition = $this->resources[$resource];
        abort_unless($request->user()?->can($definition['permission']), 403);

        /** @var Model $model */
        $model = $definition['model']::query()->findOrFail($id);
        $field = $definition['field'];
        $current = $model->getAttribute($field);

        if (isset($definition['active'], $definition['inactive'])) {
            $model->forceFill([$field => $current === $definition['active'] ? $definition['inactive'] : $definition['active']])->save();
        } else {
            $model->forceFill([$field => ! (bool) $current])->save();
        }

        return back()->with('status', 'Status data berhasil diperbarui.');
    }
}
