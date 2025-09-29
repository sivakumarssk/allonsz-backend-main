            <select name="mandal" class="form-control" id="mandal" required>
                <option value="">--Select Mandal--</option>
                @forelse($mandals as $mandal)
                  <option value="{{$mandal->id}}" {{$mandal->id == $customer->mandal_id ? 'selected' : ''}}>{{$mandal->name}}</option>
                @empty
                @endforelse
            </select>