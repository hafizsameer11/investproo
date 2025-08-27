<div class="mb-3">
    <label for="plan_name" class="form-label">Plan Name</label>
    <input type="text" name="plan_name" id="plan_name" class="form-control" 
           value="{{ old('plan_name', $plan->plan_name ?? '') }}" required>
</div>

<div class="mb-3">
    <label for="min_amount" class="form-label">Min Amount</label>
    <input type="number" step="0.01" name="min_amount" id="min_amount" class="form-control" 
           value="{{ old('min_amount', $plan->min_amount ?? '') }}" required>
</div>

<div class="mb-3">
    <label for="max_amount" class="form-label">Max Amount</label>
    <input type="number" step="0.01" name="max_amount" id="max_amount" class="form-control" 
           value="{{ old('max_amount', $plan->max_amount ?? '') }}" required>
</div>

<div class="mb-3">
    <label for="profit_percentage" class="form-label">Profit Percentage</label>
    <input type="number" step="0.01" name="profit_percentage" id="profit_percentage" class="form-control" 
           value="{{ old('profit_percentage', $plan->profit_percentage ?? '') }}" required>
</div>

<div class="mb-3">
    <label for="duration" class="form-label">Duration (Month)</label>
    <input type="number" name="duration" id="duration" class="form-control" 
           value="{{ old('duration', $plan->duration ?? '') }}" required>
</div>

{{-- <div class="mb-3">
    <label for="status" class="form-label">Status</label>
    <select name="status" id="status" class="form-select" required>
        <option value="active" {{ (old('status', $plan->status ?? '') == 'active') ? 'selected' : '' }}>Active</option>
        <option value="inactive" {{ (old('status', $plan->status ?? '') == 'inactive') ? 'selected' : '' }}>Inactive</option>
    </select> --}}
{{-- </div> --}}
