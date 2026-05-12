<div class="grid-two">
    <label>
        <span>Code</span>
        <input type="text" name="code" value="{{ old('code', $addon->code ?? '') }}" required>
    </label>

    <label>
        <span>Name</span>
        <input type="text" name="name" value="{{ old('name', $addon->name ?? '') }}" required>
    </label>

    <label>
        <span>Unit Type</span>
        <select name="unit_type" required>
            <option value="">Select add-on effect type</option>
            @foreach($unitTypeOptions as $key => $label)
                <option value="{{ $key }}" {{ old('unit_type', $addon->unit_type ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </label>

    <label>
        <span>Price (IDR)</span>
        <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $addon->price ?? 0) }}" required>
    </label>

    <label>
        <span>Included Units</span>
        <input type="number" min="0" name="included_units" value="{{ old('included_units', $addon->included_units ?? 0) }}" required>
    </label>

    <div class="addon-checkbox-wrap">
        <label class="addon-checkbox-item">
            <input type="checkbox" name="is_recurring" value="1" {{ old('is_recurring', $addon->is_recurring ?? true) ? 'checked' : '' }}>
            <span>Recurring add-on</span>
        </label>
        <label class="addon-checkbox-item">
            <input type="checkbox" name="active" value="1" {{ old('active', $addon->active ?? true) ? 'checked' : '' }}>
            <span>Active</span>
        </label>
    </div>
</div>
