            <select name="vmodel" class="form-control vmodel" id="vmodel" required>
                <option value="">--Select--</option>
                @forelse($vmodels as $model)
                <option value="{{$model->id}}">{{$model->name}}</option>
                @empty
                @endforelse
            </select>