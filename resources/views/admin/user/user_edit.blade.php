@extends('backend.admin.layout')
@section('admin-dasboard-content')
<div class="container-xxl">
    <div class="row justify-content-center">
        <div class="col-md-12 col-lg-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="card-title">Edit Users</h4>
                        </div><!--end col-->
                    </div> <!--end row-->
                </div><!--end card-header-->
                <div class="card-body pt-0">
                    <form method="POST" action="{{ route('users.update', $user->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="mb-3 row">
                            <label for="horizontalInput1" class="col-sm-2 col-form-label">Username</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" id="horizontalInput1" value="{{$user->name}}" name="name" placeholder="Enter UserName">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="horizontalInput1" class="col-sm-2 col-form-label">Email</label>
                            <div class="col-sm-10">
                                <input type="email" class="form-control" id="horizontalInput1" value="{{$user->email}}" name="email" placeholder="Enter Email">
                            </div>
                        </div>

                        @php
                        $roles=DB::table('users')->select('roles_id')->where('id',$user->id)->get();
                        @endphp
                        <div class="mb-3 row">
                            <label for="horizontalInput1" class="col-sm-2 col-form-label">Role</label>
                            <div class="col-sm-10">
                                <select class="form-select" name="role" aria-label="Default select example">
                                    <option selected="">Select Role</option>

                                    @foreach($roles as $role)
                                    <option value="1" {{(($role->roles_id=='1') ? 'selected' : '')}}>Admin</option>
                                    <option value="2" {{(($role->roles_id=='2') ? 'selected' : '')}}>Broker</option>
                                    <option value="3" {{(($role->roles_id=='3') ? 'selected' : '')}}>Seller</option>
                                    <option value="4" {{(($role->roles_id=='4') ? 'selected' : '')}}>Buyer</option>
                                    <option value="5" {{(($role->roles_id=='5') ? 'selected' : '')}}>Employee</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="horizontalInput1" class="col-sm-2 col-form-label">Status</label>
                            <div class="col-sm-10">
                                <select class="form-select" name="status" aria-label="Default select example">
                                    <option value="active" {{(($user->status=='active') ? 'selected' : '')}}>Active</option>
                                    <option value="inactive" {{(($user->status=='inactive') ? 'selected' : '')}}>Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-10 ms-auto">
                                <button type="submit" class="btn btn-primary">Update</button>
                                <button type="reset" class="btn btn-danger">Cancel</button>
                            </div>
                        </div>
                    </form>
                </div><!--end card-body-->
            </div><!--end card-->
        </div> <!--end col-->
    </div><!--end row-->
</div>
@endsection