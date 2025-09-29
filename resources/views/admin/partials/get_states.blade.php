            <select name="state" class="form-control" id="state" required>
                <option value="">--Select State--</option>
                @forelse($states as $state)
                  <option value="{{$state->id}}" {{$state->id == $customer->state_id ? 'selected' : ''}}>{{$state->name}}</option>
                @empty
                @endforelse
            </select>