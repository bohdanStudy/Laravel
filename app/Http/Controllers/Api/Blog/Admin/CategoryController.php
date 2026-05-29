<?php

namespace App\Http\Controllers\Api\Blog\Admin;

//use App\Http\Controllers\Controller;
use App\Http\Requests\BlogCategoryCreateRequest;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Requests\BlogCategoryUpdateRequest;

class CategoryController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        //dd(__METHOD__);
        $paginator = BlogCategory::paginate(5);

        return $paginator;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BlogCategoryCreateRequest $request)
    {
        $data = $request->input(); // отримаємо масив даних, які надійшли з форми

        if (empty($data['slug'])) { // якщо псевдонім порожній
            $data['slug'] = Str::slug($data['title']); // генеруємо псевдонім
        }

        $item = (new BlogCategory())->create($data); // створюємо об'єкт і додаємо в БД

        if ($item) {
            return [
                'success' => true,
                'message' => 'Успішно збережено'
            ];
        } else {
            return ['message' => 'Помилка збереження'];
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BlogCategoryUpdateRequest $request, $id)
    {
        //
        $item = BlogCategory::find($id);
        if (empty($item)) { //якщо ід не знайдено
            return back() //redirect back
            ->withErrors(['msg' => "Запис id=[{$id}] не знайдено"]) //видати помилку
            ->withInput(); //повернути дані
        }

        $data = $request->all(); //отримаємо масив даних, які надійшли з форми
        if (empty($data['slug'])) { //якщо псевдонім порожній
            $data['slug'] = Str::slug($data['title']); //генеруємо псевдонім
        }

        $result = $item->update($data);  //оновлюємо дані об'єкта і зберігаємо в БД

        if ($result) {
            return ['success' => 'Успішно збережено', 'item' => $item];
        } else {
            return ['msg' => 'Помилка збереження'];
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
