            
            
            <div class="model_modal">
                <div class="row">
                    @forelse($vmodels as $model)
                        <div class="col-md-3 mt-2 mb-4 model-item" data-dismiss="modal" data-id="{{$model->id}}">
                            <h6>{{$model->name}}</h6>
                            <img src="{{asset('public/images/vmodels/'.$model->image)}}" alt="N/A" class="w-100">
                        </div>
                    @empty
                    @endforelse
                </div>
            </div>