            <select name="country" class="form-control" id="country" required>
                <option value="">--Select Country--</option>
                @forelse($countries as $country)
                  <option value="{{$country->id}}" {{$country->id == $customer->country_id ? 'selected' : ''}}>{{$country->name}}</option>
                @empty
                @endforelse
            </select>