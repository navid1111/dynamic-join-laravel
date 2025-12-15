<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configure Transformations</title>
    
    <!-- CSS Dependencies -->
    <link rel="stylesheet" href="{{ asset('modules/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/container.css') }}">
    <!-- Using FontAwesome for icons if available, else text Fallback -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    <style>
        .container { max-width: 1200px; margin-top: 30px; }
        .card-header { background-color: #f8f9fa; }
    </style>
</head>
<body>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2>Configure Column Transformations: {{ $report->name }}</h2>
            <!-- END DEBUG -->
            <p class="text-muted">
                Transformations change how data is displayed without affecting the database or filters.
            </p>
            
            <form method="POST" action="{{ route('reports.transformations.store', $report) }}">
                @csrf
                
                <div id="transformations-container">
                    @foreach($columns as $index => $column)
                        @php
                            $hasTransformation = isset($currentTransformations[$column['key']]);
                            $activeTransformers = $hasTransformation 
                                ? $currentTransformations[$column['key']]['transformers'] 
                                : [];
                        @endphp
                        
                        <div class="card mb-3 column-transformation" 
                             data-column="{{ $column['key'] }}">
                            <div class="card-header">
                                <div class="form-check">
                                    <input class="form-check-input column-toggle" 
                                           type="checkbox" 
                                           id="enable_{{ $index }}"
                                           {{ $hasTransformation ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" 
                                           for="enable_{{ $index }}">
                                        {{ $column['display'] }}
                                    </label>
                                </div>
                            </div>
                            
                            <div class="card-body transformer-config" 
                                 style="display: {{ $hasTransformation ? 'block' : 'none' }};">
                                
                                <input type="hidden" 
                                       name="transformations[{{ $index }}][column]" 
                                       value="{{ $column['key'] }}">
                                
                                <div class="transformers-list">
                                    @foreach($activeTransformers as $tIndex => $transformer)
                                        <div class="transformer-item border p-3 mb-2">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label>Transformer</label>
                                                    <select class="form-select transformer-select"
                                                            name="transformations[{{ $index }}][transformers][{{ $tIndex }}][name]">
                                                        <option value="">Select Transformer...</option>
                                                        @foreach($transformers as $category => $categoryTransformers)
                                                            <optgroup label="{{ $category }}">
                                                                @foreach($categoryTransformers as $name => $trans)
                                                                    <option value="{{ $name }}"
                                                                            {{ $transformer['name'] === $name ? 'selected' : '' }}>
                                                                        {{ $trans->getDescription() }}
                                                                    </option>
                                                                @endforeach
                                                            </optgroup>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-5">
                                                    <label>Options</label>
                                                    <div class="transformer-options">
                                                        <!-- Dynamic options will be loaded here -->
                                                    </div>
                                                </div>
                                                <div class="col-md-1">
                                                    <label>&nbsp;</label>
                                                    <button type="button" 
                                                            class="btn btn-danger btn-sm remove-transformer">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                
                                <button type="button" 
                                        class="btn btn-sm btn-secondary add-transformer"
                                        data-column-index="{{ $index }}">
                                    <i class="fas fa-plus"></i> Add Transformer
                                </button>
                                
                                <div class="preview-section mt-3">
                                    <label>Preview</label>
                                    <div class="input-group">
                                        <input type="text" 
                                               class="form-control preview-input" 
                                               placeholder="Enter sample value...">
                                        <button type="button" 
                                                class="btn btn-primary preview-btn">
                                            Preview
                                        </button>
                                    </div>
                                    <div class="preview-result mt-2"></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Save Transformations</button>
                    <a href="{{ route('viewReport.index', $report->id) }}" 
                       class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<template id="transformer-template">
    <div class="transformer-item border p-3 mb-2">
        <div class="row">
            <div class="col-md-6">
                <label>Transformer</label>
                <select class="form-select transformer-select" name="transformations[INDEX][transformers][T_INDEX][name]">
                    <option value="">Select Transformer...</option>
                    @foreach($transformers as $category => $categoryTransformers)
                        <optgroup label="{{ $category }}">
                            @foreach($categoryTransformers as $name => $trans)
                                <option value="{{ $name }}">{{ $trans->getDescription() }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>
            <div class="col-md-5">
                <label>Options</label>
                <div class="transformer-options"></div>
            </div>
            <div class="col-md-1">
                <label>&nbsp;</label>
                <button type="button" class="btn btn-danger btn-sm remove-transformer">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
    window.columnTypes = @json($columnTypes ?? []);
    window.typeCompatibility = @json($typeCompatibility ?? []);
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle column transformation
    document.querySelectorAll('.column-toggle').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const config = this.closest('.column-transformation')
                              .querySelector('.transformer-config');
            config.style.display = this.checked ? 'block' : 'none';
            
            // Auto-add one transformer if checked and empty?
            const list = config.querySelector('.transformers-list');
            if (this.checked && list.children.length === 0) {
                 const container = this.closest('.column-transformation');
                 addTransformer(list, this.id.replace('enable_', ''), container.dataset.column);
            }
        });
    });
    
    function addTransformer(list, columnIndex, columnKey) {
        const template = document.getElementById('transformer-template').content.cloneNode(true);
        const item = template.querySelector('.transformer-item');
        
        // Filter Options based on Type
        // If we have type info for this column
        if (window.columnTypes && window.columnTypes[columnKey]) {
            const colType = window.columnTypes[columnKey];
            const select = item.querySelector('select');
            
            select.querySelectorAll('optgroup').forEach(group => {
                const category = group.label;
                const compatibleTypes = window.typeCompatibility[category] || [];
                
                // Check if compatible
                const isCompatible = compatibleTypes.includes('*') || compatibleTypes.includes(colType);
                
                if (!isCompatible) {
                    group.remove(); // Remove entire incompatible group
                }
            });
        }
        
        // Update Name Indices
        // Template uses placeholders: INDEX and T_INDEX
        const tIndex = list.children.length;
        
        item.querySelectorAll('[name]').forEach(input => {
            input.name = input.name
                .replace('INDEX', columnIndex)
                .replace('T_INDEX', tIndex);
        });
        
        list.appendChild(item);
    }
    
    // Add transformer button click
    document.querySelectorAll('.add-transformer').forEach(btn => {
        btn.addEventListener('click', function() {
            const columnIndex = this.dataset.columnIndex;
            const container = this.closest('.column-transformation');
            const list = this.previousElementSibling;
            addTransformer(list, columnIndex, container.dataset.column);
        });
    });
    
    // Remove transformer
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-transformer')) {
            const item = e.target.closest('.transformer-item');
            const list = item.parentElement;
            // Allow removing even the last one, as long as the toggle is checked? 
            // Or if toggle is checked we expect at least one?
            // User can uncheck toggle to disable all. So let's allow removing all.
            item.remove();
        }
    });
    
    // Preview transformation
    document.querySelectorAll('.preview-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const container = this.closest('.column-transformation');
            const input = container.querySelector('.preview-input');
            const result = container.querySelector('.preview-result');
            const transformers = []; 
            
            container.querySelectorAll('.transformer-item').forEach(item => {
                 const select = item.querySelector('.transformer-select');
                 if(select && select.value) {
                     transformers.push({ name: select.value, options: {} });
                 }
            });
            
            if (transformers.length === 0) {
                result.innerHTML = '<div class="text-warning">Select a transformer first.</div>';
                return;
            }

            try {
                const response = await fetch('{{ route("reports.transformations.preview") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        value: input.value,
                        transformers: transformers 
                    })
                });
                
                const data = await response.json();
                result.innerHTML = `
                    <div class="alert alert-info p-2 mt-2">
                        <strong>Original:</strong> ${data.original}<br>
                        <strong>Result:</strong> ${data.transformed}
                    </div>
                `;
            } catch (e) {
                result.innerHTML = '<div class="text-danger">Error previewing.</div>';
            }
        });
    });
});
</script>
</body>
</html>
