<?php

use App\Models\Branch;

if (!function_exists('current_branch_id')) {
    function current_branch_id(): ?int
    {
        $id = session('current_branch_id');

        return $id ? (int) $id : null;
    }
}

if (!function_exists('current_branch')) {
    function current_branch(): ?Branch
    {
        $id = current_branch_id();

        if (!$id) {
            return null;
        }

        return Branch::find($id);
    }
}