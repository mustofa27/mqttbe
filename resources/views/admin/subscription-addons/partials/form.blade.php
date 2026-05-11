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
        <input type="text" name="unit_type" value="{{ old('unit_type', $addon->unit_type ?? '') }}" required>
    </label>

    <label>
        <span>Price (IDR)</span>
        <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $addon->price ?? 0) }}" required>
    </label>

    <label>
        <span>Included Units</span>
        <input type="number" min="0" name="included_units" value="{{ old('included_units', $addon->included_units ?? 0) }}" required>
    </label>

    <div class="checkbox-wrap">
        <label class="checkbox-item">
            <input type="checkbox" name="is_recurring" value="1" {{ old('is_recurring', $addon->is_recurring ?? true) ? 'checked' : '' }}>
            <span>Recurring add-on</span>
        </label>
        <label class="checkbox-item">
            <input type="checkbox" name="active" value="1" {{ old('active', $addon->active ?? true) ? 'checked' : '' }}>
            <span>Active</span>
        </label>
    </div>
</div>

<style>
.grid-two { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.grid-two label { display: grid; gap: 0.35rem; }
.grid-two input[type="text"], .grid-two input[type="number"] { border: 1px solid #cbd5e1; border-radius: 6px; padding: 0.55rem; }
.checkbox-wrap { display: grid; gap: 0.5rem; align-content: center; }
.checkbox-item { display: flex; align-items: center; gap: 0.5rem; }
@media (max-width: 800px) { .grid-two { grid-template-columns: 1fr; } }
</style>
