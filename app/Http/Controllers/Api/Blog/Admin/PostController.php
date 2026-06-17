<?php

namespace App\Http\Controllers\Api\Blog\Admin;

use App\Models\BlogPost;
use App\Repositories\BlogPostRepository;
use App\Repositories\BlogCategoryRepository;
use App\Http\Requests\BlogPostCreateRequest;
use App\Http\Requests\BlogPostUpdateRequest;
use App\Http\Resources\Api\Blog\Admin\PostResource;
use App\Jobs\BlogPostAfterCreateJob;
use App\Jobs\BlogPostAfterDeleteJob;

class PostController extends BaseController
{
    public function __construct(
        private BlogPostRepository $blogPostRepository,
        private BlogCategoryRepository $blogCategoryRepository
    ) {
        //parent::__construct();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $paginator = $this->blogPostRepository->getAllWithPaginate();

        return PostResource::collection($paginator);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BlogPostCreateRequest $request) // Повернули валідацію
    {
        $data = $request->input();

        $item = (new BlogPost())->create($data);

        if ($item) {
            $job = new BlogPostAfterCreateJob($item);

            // ВИПРАВЛЕНО: використовуємо глобальну функцію dispatch замість $this->dispatch
            dispatch($job);

            return [
                'success' => true,
                'message' => "Успішно збережено", // Виправив massage на message
                'data' => $item
            ];
        } else {
            return[
                'success' => false,
                'message' => "Помилка збереження"
            ];
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
    public function update(BlogPostUpdateRequest $request, string $id)
    {
        $item = $this->blogPostRepository->getEdit($id);

        if (empty($item)) { //якщо ід не знайдено
            return ['message' => "Запис id=[{$id}] не знайдено"];
        }

        $data = $request->all(); //отримаємо масив даних, які надійшли з форми

        $result = $item->update($data); //оновлюємо дані об'єкта і зберігаємо в БД

        if ($result) {
            return [
                'success' => true,
                'message' => 'Успішно збережено'
            ];
        } else {
            return ['message' => 'Помилка збереження'];
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $result = BlogPost::destroy($id); //софт деліт, запис лишається

        //$result = BlogPost::find($id)->forceDelete(); //повне видалення з БД

        if ($result) {
            BlogPostAfterDeleteJob::dispatch($id)->delay(20);
            return [
                'success' => true,
                'message' => 'Успішно видалено'
            ]; //TODO: Написати код респонса
        } else {
            return [
                'success' => false,
                'message' => 'Помилка'
            ]; //TODO: Написати код респонса
        }
    }
}
