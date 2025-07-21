<form action="{{ route('users.store') }}" method="POST">
    @csrf
    <input type="text" name="name" placeholder="Name" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>

    <select name="institute_id" required>
        @foreach ($institutes as $institute)
            <option value="{{ $institute->id }}">{{ $institute->name }}</option>
        @endforeach
    </select>

    <button>Submit</button>
</form>
