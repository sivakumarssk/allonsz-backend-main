            <select name="district" class="form-control" id="district" required>
                <option value="">--Select District--</option>
                @forelse($districts as $district)
                  <option value="{{$district->id}}" {{$district->id == $customer->district_id ? 'selected' : ''}}>{{$district->name}}</option>
                @empty
                @endforelse
            </select>