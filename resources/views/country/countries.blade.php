<div class="mb-3">
    <label>Country</label>
    <input type="text" name="country" class="form-control" value="{{ old('country', $country->country ?? '') }}" required>
</div>

<div class="mb-3">
    <label>Currency</label>
    <input type="text" name="currency" class="form-control" value="{{ old('currency', $country->currency ?? '') }}" required>
</div>

<div class="mb-3">
    <label>Symbol</label>
    <input type="text" name="symbol" class="form-control" value="{{ old('symbol', $country->symbol ?? '') }}" required>
</div>

<div class="mb-3">
    <label>ISO Code</label>
    <input type="text" name="iso_code" class="form-control" maxlength="3" value="{{ old('iso_code', $country->iso_code ?? '') }}" required>
</div>

<div class="mb-3">
    <label>Exchange Rate</label>
    <input type="number" step="0.0001" name="exchange_rate" class="form-control" value="{{ old('exchange_rate', $country->exchange_rate ?? 1) }}" required>
</div>

<div class="mb-3">
    <label>Status</label>
    <select name="status" class="form-control" required>
        <option value="1" {{ (old('status', $country->status ?? 1) == 1) ? 'selected' : '' }}>Active</option>
        <option value="0" {{ (old('status', $country->status ?? 1) == 0) ? 'selected' : '' }}>Inactive</option>
    </select>
</div>

<div class="mb-3">
    <label>Default</label>
    <select name="default" class="form-control" required>
        <option value="0" {{ (old('default', $country->default ?? 0) == 0) ? 'selected' : '' }}>No</option>
        <option value="1" {{ (old('default', $country->default ?? 0) == 1) ? 'selected' : '' }}>Yes</option>
    </select>
</div>
