# Simple BEM in SASS

Using SASS nesting and character escaping in classes allows you to create some clean front end code. In the example below, we have Blocks (List), Elements (item, link) and Modifiers both local (--submit, --emphasis) and global (+button)

```html
    <ul class="List">
        <li class="List__item">
            <a class="List__link --submit +button" href="#">Contact Us</a>
        </li>
        <li class="List__item">
            <a class="List__link" href="#">A Link</a>
        </li>
        <li class="List__item">
            <a class="List__link --emphasis" href="#">Another Link</a>
        </li>
    </ul>
```
