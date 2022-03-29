<ul class="nav flex-column">
@can("post-viewer")
    <li class="nav-item">
        <a class="nav-link {{ (request()->segment(1) == 'posts') ? 'active' : '' }}" href="/posts/">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path><polyline points="13 2 13 9 20 9"></polyline></svg>
            Instagram
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ (request()->segment(1) == 'stories') ? 'active' : '' }}" href="/stories/">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path><polyline points="13 2 13 9 20 9"></polyline></svg>
            Сторис
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ (request()->segment(1) == 'persons') ? 'active' : '' }}" href="/persons/">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-users"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            Персоны
        </a>
    </li>


    <li class="nav-item ml-4">
        <a class="nav-link {{ (request()->segment(1) == 'sources') ? 'active' : '' }}"  href="/sources/">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-users"><circle cx="12" cy="12" r="6"></circle></svg>
            Аккаунты в соцсетях
        </a>
    </li>
@endcan

@can("article-viewer")
    <li class="nav-item">
        <a class="nav-link {{ ((request()->segment(1) == 'articles')&&(!(request()->segment(2)))) ? 'active' : '' }}" href="/articles/">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file-text"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
            Статьи конкурентов
        </a>
    </li>
@endcan

@can("admin")
    <li class="nav-item ml-4">
        <a class="nav-link {{ (request()->segment(1) == 'rss-category') ? 'active' : '' }}"  href="/rss-category/">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-users"><circle cx="12" cy="12" r="6"></circle></svg>
            Категории статей
        </a>
    </li>

    <li class="nav-item ml-4">
        <a class="nav-link {{ (request()->segment(1) == 'rss-sources') ? 'active' : '' }}"  href="/rss-sources/">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-users"><circle cx="12" cy="12" r="6"></circle></svg>
            Конкуренты
        </a>
    </li>
@endcan
@can("article-viewer")
        <li class="nav-item ml-4">
            <a class="nav-link {{ (request()->segment(1) == 'articles-rewrite') ? 'active' : '' }}"  href="/articles-rewrite/">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-users"><circle cx="12" cy="12" r="6"></circle></svg>
                Рерайт статей
            </a>
        </li>


        <li class="nav-item ml-4">
            <a class="nav-link {{ ((request()->segment(1) == 'articles')&&( request()->segment(2) === 'buckingham')) ? 'active' : '' }}"  href="/articles/buckingham/">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-users"><circle cx="12" cy="12" r="6"></circle></svg>
                Британский королевский дом
            </a>
        </li>
@endcan
    <li class="nav-item">
        <a class="nav-link {{ (request()->segment(1) == 'trends') ? 'active' : '' }}" href="/trends/">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file-text"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
            Google Trends
        </a>
    </li>

@auth
    <li class="nav-item">
        <a class="nav-link" href="https://zenstat.ru/user/login/token/?tkn=1ndlYkBpbWVkaWEucnUiLCJpYX" target="_blank">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg" class="feather feather-users">
                <path d="M6 21C5.20435 21 4.44129 20.6839 3.87868 20.1213C3.31607 19.5587 3 18.7956 3 18V6C3 5.20435 3.31607 4.44129 3.87868 3.87868C4.44129 3.31607 5.20435 3 6 3H12C12.2652 3 12.5196 3.10536 12.7071 3.29289C12.8946 3.48043 13 3.73478 13 4C13 4.26522 12.8946 4.51957 12.7071 4.70711C12.5196 4.89464 12.2652 5 12 5H6C5.73478 5 5.48043 5.10536 5.29289 5.29289C5.10536 5.48043 5 5.73478 5 6V18C5 18.2652 5.10536 18.5196 5.29289 18.7071C5.48043 18.8946 5.73478 19 6 19H18C18.2652 19 18.5196 18.8946 18.7071 18.7071C18.8946 18.5196 19 18.2652 19 18V12C19 11.7348 19.1054 11.4804 19.2929 11.2929C19.4804 11.1054 19.7348 11 20 11C20.2652 11 20.5196 11.1054 20.7071 11.2929C20.8946 11.4804 21 11.7348 21 12V18C21 18.7956 20.6839 19.5587 20.1213 20.1213C19.5587 20.6839 18.7956 21 18 21H6ZM11.293 12.707C11.2 12.6141 11.1263 12.5038 11.0759 12.3824C11.0256 12.261 10.9997 12.1309 10.9997 11.9995C10.9997 11.8681 11.0256 11.738 11.0759 11.6166C11.1263 11.4952 11.2 11.3849 11.293 11.292L17.578 5.007L16 5C15.7348 5 15.4804 4.89464 15.2929 4.70711C15.1054 4.51957 15 4.26522 15 4C15 3.73478 15.1054 3.48043 15.2929 3.29289C15.4804 3.10536 15.7348 3 16 3V3L20 3.02C20.2652 3.02 20.5196 3.12536 20.7071 3.31289C20.8946 3.50043 21 3.75478 21 4.02V8C21 8.26522 20.8946 8.51957 20.7071 8.70711C20.5196 8.89464 20.2652 9 20 9C19.7348 9 19.4804 8.89464 19.2929 8.70711C19.1054 8.51957 19 8.26522 19 8V6.415L12.707 12.707C12.5195 12.8945 12.2652 12.9998 12 12.9998C11.7348 12.9998 11.4805 12.8945 11.293 12.707V12.707Z"/>
            </svg>
            Посты Яндекс Дзен
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ (request()->segment(1) == 'liveinternet') ? 'active' : '' }}" href="/liveinternet/">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file-text"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
            LiveInternet
        </a>
    </li>

        <li class="nav-item ml-4">
            <a class="nav-link {{ (request()->segment(1) == 'liveinternet-pages') ? 'active' : '' }}"  href="/liveinternet-pages/">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-users"><circle cx="12" cy="12" r="6"></circle></svg>
                Статьи
            </a>
        </li>
@endauth
    <li class="nav-item">
        <a class="nav-link {{ (request()->segment(1) == 'calendar') ? 'active' : '' }}" href="/calendar/">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file-text"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
            Календарь событий
        </a>
    </li>

@can("admin")
    <li class="nav-item ml-4">
        <a class="nav-link {{ (request()->segment(1) == 'regular-events') ? 'active' : '' }}"  href="/regular-events/">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-users"><circle cx="12" cy="12" r="6"></circle></svg>
            Редактирование событий
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ (request()->segment(1) == 'users') ? 'active' : '' }}" href="/users/">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-users"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            Пользователи
        </a>
    </li>
@endcan

@can("admin")
    <li class="nav-item">
        <a class="nav-link {{ (request()->segment(1) == 'instagram') ? 'active' : '' }}" href="/instagram/edit/">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file-text"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
            Парсер Instagram
        </a>
    </li>
@endcan
</ul>
