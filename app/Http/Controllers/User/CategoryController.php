<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Display a listing of the user's categories.
     */
    public function index(): View
    {
        $categories = Category::forUser(Auth::id())
            ->orderBy('name')
            ->get();

        return view('categories.index', compact('categories'));
    }

    /**
     * Store a newly created category.
     */
    public function store(CategoryRequest $request): RedirectResponse
    {
        Category::create([
            'user_id' => Auth::id(),
            'name' => trim($request->name),
        ]);

        return redirect()->route('categories.index')->with('status', 'Categoria criada com sucesso!');
    }

    /**
     * Update the specified category.
     */
    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        // Garante isolamento total de dados entre usuários (FR-024)
        if ($category->user_id !== Auth::id()) {
            abort(403, 'Acesso não autorizado a esta categoria.');
        }

        $category->update([
            'name' => trim($request->name),
        ]);

        return redirect()->route('categories.index')->with('status', 'Categoria atualizada com sucesso!');
    }

    /**
     * Remove the specified category.
     */
    public function destroy(Category $category): RedirectResponse
    {
        // Garante isolamento total de dados entre usuários (FR-024)
        if ($category->user_id !== Auth::id()) {
            abort(403, 'Acesso não autorizado a esta categoria.');
        }

        // Impede a exclusão de categoria que esteja em uso (FR-027)
        if ($category->isInUse()) {
            return redirect()->route('categories.index')->with(
                'error',
                'Não é possível excluir esta categoria porque ela está vinculada a um ou mais gastos. Reatribua os gastos antes de excluí-la.'
            );
        }

        $category->delete();

        return redirect()->route('categories.index')->with('status', 'Categoria excluída com sucesso!');
    }
}
