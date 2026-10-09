    @csrf

    <div class="mb-3">
        <label>Food Name</label>
        <input type="text"
            name="name"
            class="form-control"
            value="{{ old('name', $food->name ?? '') }}"
            required>
    </div>

    
    <div class="mb-3">
        <label>Category</label>
        <select name="category" class="form-select">
            @foreach(['Rice','Swallow','Soup','Snacks','Drinks','Fast Food','Others'] as $category)
                <option value="{{ $category }}"
                    {{ old('category', $food->category ?? '') == $category ? 'selected' : '' }}>
                    {{ $category }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label>Price</label>
        <input type="number"
            step="0.01"
            name="price"
            class="form-control"
            value="{{ old('price', $food->price ?? '') }}"
            required>
    </div>

    <div class="mb-3">
        <label>Status</label>
        <select name="status" class="form-select">
            <option value="in_stock"
                {{ old('status', $food->status ?? '') == 'in_stock' ? 'selected' : '' }}>
                In Stock
            </option>

            <option value="out_of_stock"
                {{ old('status', $food->status ?? '') == 'out_of_stock' ? 'selected' : '' }}>
                Out of Stock
            </option>
        </select>
    </div>

    @if(isset($food) && $food->image)
        <div class="mb-3">
            <img src="{{ asset('images/foods/'.$food->image) }}"
                width="150"
                class="img-thumbnail">
        </div>
    @endif

    <div class="mb-3">
        <label>Image</label>
        <input type="file"
            name="image"
            class="form-control">
    </div>

    <button class="btn btn-success">
        {{ isset($food) ? 'Update Food' : 'Add Food' }}
    </button>