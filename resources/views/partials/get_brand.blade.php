            <select name="brand" class="form-control brand" id="brand" required>
                <option value="">--Select--</option>
                @forelse($brands as $brand)
                <option value="{{$brand->id}}" {{$brand->id == $brand_id ? 'selected' : ''}}>{{$brand->name}}</option>
                @empty
                @endforelse
            </select>