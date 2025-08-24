<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Chain;
use Exception;

class ChainController extends Controller
{
    /**
     * Get all active chains with wallet addresses
     */
    public function index()
    {
        try {
            $chains = Chain::where('status', 'active')
                ->select('id', 'type', 'address', 'status')
                ->get();
                
            return ResponseHelper::success($chains, 'Chains retrieved successfully');
        } catch (Exception $ex) {
            return ResponseHelper::error('Failed to retrieve chains: ' . $ex->getMessage());
        }
    }

    /**
     * Get a specific chain by ID
     */
    public function show($id)
    {
        try {
            $chain = Chain::findOrFail($id);
            return ResponseHelper::success($chain, 'Chain retrieved successfully');
        } catch (Exception $ex) {
            return ResponseHelper::error('Chain not found: ' . $ex->getMessage());
        }
    }
}
