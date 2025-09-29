            <select name="account" class="form-control select2 select2 select2-hidden-accessible account" style="width:100%" id="account">
                <option value="">Choose Account</option>
                @forelse($accounts as $account)
                    <option value="{{$account->id}}" {{$account->id == $account_id ? 'selected' : ''}}>
                    {{$account->bank}} - {{$account->number}} - {{$account->holder}} - {{$account->ifsc}}
                    </option>
                @empty
                @endforelse
            </select>