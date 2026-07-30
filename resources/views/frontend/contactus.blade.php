<!DOCTYPE html>
<html lang="en">

<head>
    @include('components.frontend.head')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/18.2.1/css/intlTelInput.css">
    <style>
      .iti { width: 100%; }

      /* Submit loader overlay */
      #submitLoader {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.9);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        flex-direction: column;
      }
      #submitLoader p {
        margin-top: 15px;
        font-size: 16px;
        color: #333;
      }
    </style>
</head>

<body class="index-page">

  <header id="header" class="header sticky-top">
    @include('components.frontend.header')
  </header>

  <main class="main">

    <section class="ddpu-breadcrumb-sec">
      <div class="container">
        <div class="row">
          <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
            <h1>Contact</h1>
            <ul class="bread-list">
              <li><a href="./">Home<i class="fa fa-angle-right"></i></a></li>
              <li class="active"><a href="javascript:void(0)">Contact</a></li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <section class="contact-us-form-sec">
      <div class="container">
        <div class="row">

          <!-- LEFT SIDE -->
          <div class="col-md-6">
            <div class="contact-us-form-title-sec">
              <h2>Questions? Send Us a Message!</h2>
            </div>

            <div class="contact-us-email-call-loaction-sec">

              <!-- PHONE -->
              <div class="contact-us-one-sec">
                <div class="contact-us-cea-sec">
                  <div class="row">
                    <div class="col-12 col-md-2">
                      <div class="con-cea-img-sec">
                        <i class="fa-solid fa-phone"></i>
                      </div>
                    </div>
                    <div class="col-12 col-md-10">
                      <div class="con-cea-cont-sec">
                        <h3>Call Us</h3>
                        <p>
                          <a href="tel:+44 {{ $contact->phone ?? '' }}">
                            {{ $contact->phone ?? '' }}
                          </a>
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- EMAIL -->
              <div class="contact-us-one-sec">
                <div class="contact-us-cea-sec">
                  <div class="row">
                    <div class="col-12 col-md-2">
                      <div class="con-cea-img-sec">
                        <i class="fa-solid fa-envelope"></i>
                      </div>
                    </div>
                    <div class="col-12 col-md-10">
                      <div class="con-cea-cont-sec">
                        <h3>Email</h3>
                        <p>
                          <a href="mailto:{{ $contact->email ?? '' }}">
                            {{ $contact->email ?? '' }}
                          </a>
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- ADDRESS -->
              <div class="contact-us-one-sec">
                <div class="contact-us-cea-sec">
                  <div class="row">
                    <div class="col-12 col-md-2">
                      <div class="con-cea-img-sec">
                        <i class="fa-solid fa-location-dot"></i>
                      </div>
                    </div>
                    <div class="col-12 col-md-10">
                      <div class="con-cea-cont-sec">
                        <h3>Location</h3>
                        <p>
                          @if(!empty($contact->address))
                            <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($contact->address) }}" target="_blank">
                              {{ $contact->address }}
                            </a>
                          @endif
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>

          <!-- RIGHT SIDE FORM -->
          <div class="col-md-6">
            <div class="contact-form-main-sec">

              {{-- Success / error flash messages --}}
              @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
              @endif

              @if($errors->any())
                <div class="alert alert-danger">
                  <ul class="mb-0">
                    @foreach($errors->all() as $error)
                      <li>{{ $error }}</li>
                    @endforeach
                  </ul>
                </div>
              @endif

              <form class="contact-form" method="POST" action="{{ route('contact.send') }}" id="contactForm">
                @csrf

                <div class="mb-3">
                  <input type="text" name="name" class="form-control" placeholder="Your name*"
                         value="{{ old('name') }}" required>
                </div>

                <div class="mb-3">
                  <input type="email" name="email" class="form-control" placeholder="Your email*"
                         value="{{ old('email') }}" required>
                </div>

                <div class="mb-3">
                  <input type="tel" id="phone" name="phone" class="form-control" placeholder="Your phone number*"
                         value="{{ old('phone') }}">
                  <input type="hidden" name="phone_full" id="phone_full">
                </div>

                <div class="mb-3">
                  <input type="text" name="subject" class="form-control" placeholder="Subject*"
                         value="{{ old('subject') }}" required>
                </div>

                <div class="mb-3">
                  <textarea name="message" class="form-control" rows="8" placeholder="Your message*" required>{{ old('message') }}</textarea>
                </div>

                <!-- reCAPTCHA -->
                <div class="mb-3 d-flex justify-content-center">
                  <div class="g-recaptcha" data-sitekey="6LeYGlgtAAAAAKr_JlgFNntA3NFU-3O61kvcMaHm"></div>
                </div>
                @error('g-recaptcha-response')
                  <div class="text-danger mb-2">{{ $message }}</div>
                @enderror

                <button type="submit" class="contact-form-btn w-100">Submit</button>
              </form>

            </div>
          </div>

        </div>
      </div>
    </section>

    <div class="contact-us-map-sec">
      @if(!empty($contact->mapurl))
        {!! $contact->mapurl !!}
      @else
        <iframe
          src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1853.0191178120363!2d-2.3906622242440942!3d53.45372916668446!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x487ba95f5ba16c29%3A0xb9c55b4d10c165c!2sThe%20Doctors%20and%20Dentists%20Protection%20Union!5e1!3m2!1sen!2sin!4v1767787978437!5m2!1sen!2sin"
          style="border:0;"
          allowfullscreen=""
          loading="lazy"
          referrerpolicy="no-referrer-when-downgrade">
        </iframe>
      @endif
    </div>

    @include('frontend.includes.join-membership')

  </main>

  @include('components.frontend.footer')

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center">
    <i class="bi bi-arrow-up-short"></i>
  </a>

  <!-- Submit loader overlay -->
  <div id="submitLoader">
    <div class="spinner-border text-primary" role="status" style="width:3rem; height:3rem;">
      <span class="visually-hidden">Loading...</span>
    </div>
    <p>Please wait, sending your message…</p>
  </div>

  @include('components.frontend.main-js')

  <!-- reCAPTCHA script -->
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>

  <!-- intl-tel-input -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/18.2.1/js/intlTelInput.min.js"></script>
  <script>
    const phoneInput = document.querySelector("#phone");
    const iti = window.intlTelInput(phoneInput, {
      initialCountry: "gb",              // default United Kingdom
      preferredCountries: ["gb", "in"],  // show these at top
      separateDialCode: true,            // shows +44 next to the flag
      utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/18.2.1/js/utils.js"
    });
  </script>

  <!-- Form Validation + phone capture + loader + double-submit prevention -->
  <script>
  document.getElementById('contactForm').addEventListener('submit', function (e) {
      let form    = e.target;
      let name    = form.name.value.trim();
      let email   = form.email.value.trim();
      let subject = form.subject.value.trim();
      let message = form.message.value.trim();
      let btn     = form.querySelector('button[type="submit"]');

      // Required fields check
      if (!name || !email || !subject || !message) {
          alert('Please fill in all required fields.');
          e.preventDefault();
          return false;
      }

      // Basic email pattern check
      let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailPattern.test(email)) {
          alert('Please enter a valid email address.');
          e.preventDefault();
          return false;
      }

      // reCAPTCHA check
      if (typeof grecaptcha !== 'undefined' && grecaptcha.getResponse().length === 0) {
          alert('Please confirm you are not a robot.');
          e.preventDefault();
          return false;
      }

      // Store the full international phone number (e.g. +447911123456)
      if (typeof iti !== 'undefined') {
          document.getElementById('phone_full').value = iti.getNumber();
      }

      // Valid → lock the button + show loader
      btn.disabled = true;
      btn.textContent = 'Sending...';
      document.getElementById('submitLoader').style.display = 'flex';
  });
  </script>

</body>

</html>