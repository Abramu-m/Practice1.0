<div class="d-flex gap-1 w-100">
    <a href="{{ route('medications.show', $medication->id) }}" 
       class="btn btn-sm btn-info flex-fill" 
       title="View Details">
        <i class="fas fa-eye"></i>
    </a>
    
    <a href="{{ route('medications.edit', $medication->id) }}" 
       class="btn btn-sm btn-primary flex-fill" 
       title="Edit">
        <i class="fas fa-edit"></i>
    </a>
    
    <form action="{{ route('medications.destroy', $medication->id) }}" 
          method="POST" 
          style="display: inline;" 
          class="flex-fill">
        @csrf
        @method('DELETE')
        <button type="submit" 
                class="btn btn-sm btn-danger w-100" 
                onclick="return confirm('Are you sure you want to delete this medication?')"
                title="Delete">
            <i class="fas fa-trash"></i>
        </button>
    </form>
</div>
