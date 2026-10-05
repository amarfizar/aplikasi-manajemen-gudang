<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class SettingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can("settings.view"), 403);

        $defaults = [
            "company_name" => "Perusahaan Saya",
            "company_email" => "",
            "company_phone" => "",
            "company_address" => "",
            "low_stock_threshold" => "5",
            "company_logo" => "",
        ];

        $settings = Setting::pluck("value", "key")->toArray();

        // Convert logo path to full URL if exists
        if (!empty($settings["company_logo"])) {
            $settings["company_logo"] = asset("storage/" . $settings["company_logo"]);
        }

        return response()->json([
            "success" => true,
            "message" => "Pengaturan.",
            "data" => array_merge($defaults, $settings),
        ]);
    }

    public function publicSettings(): JsonResponse
    {
        $defaults = [
            "company_name" => "Inventory Gudang",
            "company_email" => "",
            "company_phone" => "",
            "company_address" => "",
            "low_stock_threshold" => "5",
            "company_logo" => "",
        ];

        $settings = Setting::pluck("value", "key")->toArray();

        // Convert logo path to full URL if exists
        if (!empty($settings["company_logo"])) {
            $settings["company_logo"] = asset("storage/" . $settings["company_logo"]);
        }

        return response()->json([
            "success" => true,
            "message" => "Pengaturan publik.",
            "data" => array_merge($defaults, $settings),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can("settings.update"), 403);

        // Debug log
        Log::info("Settings update request", [
            "content_type" => $request->header("Content-Type"),
            "method" => $request->method(),
            "all_input" => $request->all(),
            "all_files" => $request->allFiles(),
        ]);

        $data = $request->validate([
            "company_name" => ["required", "string", "max:255"],
            "company_email" => ["nullable", "string", "max:255"],
            "company_phone" => ["nullable", "string", "max:50"],
            "company_address" => ["nullable", "string"],
            "low_stock_threshold" => ["required", "integer", "min:0"],
            "company_logo" => ["nullable", "image", "mimes:jpeg,png,jpg,gif,svg,webp", "max:2048"],
        ]);

        // Handle logo upload
        if ($request->hasFile("company_logo")) {
            // Delete old logo if exists
            $oldLogo = Setting::where("key", "company_logo")->first();
            if ($oldLogo && $oldLogo->value) {
                Storage::disk("public")->delete($oldLogo->value);
            }

            $path = $request->file("company_logo")->store("settings", "public");
            $data["company_logo"] = $path;
        }

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(["key" => $key], ["value" => $value]);
        }

        $savedData = Setting::pluck("value", "key")->toArray();
        if (!empty($savedData["company_logo"])) {
            $savedData["company_logo"] = asset("storage/" . $savedData["company_logo"]);
        }

        return response()->json(["success" => true, "message" => "Pengaturan disimpan.", "data" => $savedData]);
    }
}

