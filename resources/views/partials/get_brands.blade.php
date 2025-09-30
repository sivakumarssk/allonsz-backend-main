                <div class="brands">
                  <div class="row">
                    @forelse($brands as $brand)
                        <div class="col-md-3 mt-2 mb-4 brand-item" data-dismiss="modal" data-id="{{$brand->id}}">
                            <h6 class="text-center">{{$brand->name}}</h6>
                            <img src="{{$brand->image}}" alt="N/A" class="w-100">
                        </div>
                    @empty
                    @endforelse
                  </div>
                </div>
                