# Lab 3 Student Routing, Controllers, Views, and Middleware Design

## Goal

Add the Lab 3 Student Information application to the existing LavaLust project using the required routes, controller, views, session middleware, data passing, and navigation.

## Requirements

- Keep the existing GitHub repository and LavaLust installation.
- Add a `StudentController` with public `index()` and `profile()` methods.
- Register `GET /student` and `GET /student/profile` routes.
- Add functional Student Home and Student Profile views.
- Pass student information from the controller to both views.
- Add `StudentMiddleware` to protect `/student/profile`.
- Redirect unauthorized profile requests to `/student`.
- Add working Home and Student Profile links using LavaLust's URL helper.
- Use the student's own information:
  - Name: Aljon Vincent E. Ferriol
  - Student ID: MCC2024-00076
  - Course: BS Information Technology
  - Year Level: 3rd Year
  - Section: F2
  - Email: evora.jhonj@minsu.edu.ph

## Architecture

`StudentController` owns the page actions and prepares one associative student data array. The two PHP views render that data and navigation links. `StudentMiddleware` is registered under the `student_access` alias and checks the LavaLust session value before allowing the profile route to call the controller.

The existing boot process loads `app/config/routes.php`, so that file will load `app/config/middleware.php` before registering routes. The middleware configuration will instantiate `StudentMiddleware` with LavaLust's `load_class()` helper and load the session library before the first protected request.

## Request Flow

```text
/student
  -> StudentController::index()
  -> set student_access session flag
  -> app/views/student/index.php

/student/profile
  -> student_access middleware
  -> allow when the session flag is true
  -> StudentController::profile()
  -> app/views/student/profile.php

/student/profile without access
  -> student_access middleware
  -> redirect to /student
```

## Files

- Create `app/controllers/StudentController.php`.
- Create `app/middlewares/StudentMiddleware.php`.
- Create `app/views/student/index.php`.
- Create `app/views/student/profile.php`.
- Modify `app/config/middleware.php` to register the middleware alias.
- Modify `app/config/routes.php` to load middleware configuration and register both routes.
- Modify `app/config/autoload.php` to autoload the URL helper used by navigation.

## Verification

- Run PHP lint on every new or modified PHP file.
- Run the application through PHP's built-in server and request `/student` and `/student/profile`.
- Confirm that `/student` renders the student data and sets session access.
- Confirm that `/student/profile` renders after access is granted.
- Confirm that a fresh session visiting `/student/profile` receives a redirect to `/student`.
- Confirm the final diff contains only the Lab 3 implementation and its design record.
