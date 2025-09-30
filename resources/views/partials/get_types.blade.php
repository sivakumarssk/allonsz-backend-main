                                        <div class="types">
                                          <select name="type" class="form-control type" style="width:100%" value="{{ old('type') }}" required>
                                              <option value="">--Select--</option>
                                              @forelse($category->types as $type)
                                              <option value="{{$type->id}}" {{$type->id == $type_id ? 'selected' : ''}}>{{$type->name}}</option>
                                              @empty
                                              @endforelse
                                          </select>
                                        </div>