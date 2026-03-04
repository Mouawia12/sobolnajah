<div class="table-responsive">
    <table class="table table-bordered text-center" style="width:100%">
        <thead>
            <tr>
                <th>#</th>
                <th>الاسم</th>
                <th>البريد الإلكتروني</th>
                <th>الدور</th>
                <th>المؤسسة</th>
                <th>تاريخ الإنشاء</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $index => $portalUser)
                @php
                    $roleText = $portalUser->roles
                        ->map(fn ($role) => $role->display_name ?: $role->name)
                        ->implode('، ');
                @endphp
                <tr>
                    <td>{{ $users->firstItem() + $index }}</td>
                    <td>{{ $portalUser->name }}</td>
                    <td>{{ $portalUser->email }}</td>
                    <td>{{ $roleText !== '' ? $roleText : '-' }}</td>
                    <td>{{ $portalUser->school->name_school ?? '-' }}</td>
                    <td>{{ optional($portalUser->created_at)->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">لا يوجد مستخدمون لعرضهم.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-15 d-flex justify-content-end">
    {{ $users->links() }}
</div>
