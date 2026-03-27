@extends('auth.layout')
@section('register-content')
    <div class="container-xxl">
        <div class="row vh-100 d-flex justify-content-center">
            <div class="col-12 align-self-center">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-4 mx-auto">
                            <div class="card">
                                <div class="card-body p-0 bg-black auth-header-box rounded-top">
                                    <div class="text-center p-3">
                                        <a href="index.html" class="logo logo-admin">
                                            <img src="{{ url('assets/images/admin-logo-light.jpg') }}" height="50"
                                                alt="logo" class="auth-logo">
                                        </a>
                                        <h4 class="mt-3 mb-1 fw-semibold text-white fs-18">Let's Get Started as Seller</h4>
                                    </div>
                                </div>
                                <div class="card-body pt-0">
                                    <form class="my-4" method="POST" action="{{ route('registerIn') }}">
                                        @csrf
                                        {{-- <input type="hidden" name="token" value="{{ $token }}" /> --}}

                                        <div class="form-group mb-2">
                                            <label class="form-label" for="name">Name</label>
                                            <input type="text" class="form-control" id="name" name="name"
                                                placeholder="Enter Name">
                                        </div>
                                        @error('name')
                                            <span>{{ $message }}</span>
                                        @enderror

                                        <div class="form-group mb-2">
                                            <label class="form-label" for="email">Email</label>
                                            <input type="text" class="form-control" id="email" name="email"
                                                value="" placeholder="Enter email">
                                        </div>
                                        @error('email')
                                            <span>{{ $message }}</span>
                                        @enderror

                                        <div class="form-group mb-2">
                                            <label class="form-label" for="userpassword">Password</label>
                                            <input type="password" class="form-control" name="password" id="userpassword"
                                                placeholder="Enter password">
                                        </div>
                                        @error('password')
                                            <span>{{ $message }}</span>
                                        @enderror

                                        <div class="form-group mb-2">
                                            <label class="form-label" for="userpassword">Confirm Password</label>
                                            <input type="password" class="form-control" name="password_confirmation"
                                                id="userpassword" placeholder="Confirm password">
                                        </div>

                                        <!-- Terms & Conditions Section -->
                                        <div class="form-group row mt-3">
                                            <div class="col-12">
                                                <div class="form-check form-switch form-switch-success">
                                                    <input class="form-check-input" type="checkbox" id="termsCheckbox"
                                                        disabled>
                                                    ⚠️ <strong>NDA Required — </strong>
                <a href="#" class="text-primary fw-bold" data-bs-toggle="modal" data-bs-target="#termsModal">
                    Click here to review and accept
                </a
                                                </div>
                                            </div>
                                            
                                        </div>

                                        <!-- Register Button -->
                                        <div class="form-group mb-0 row">
                                            <div class="col-12">
                                                <div class="d-grid mt-3">
                                                    <button class="btn btn-primary" type="submit" id="registerBtn"
                                                        disabled>Register <i class="fas fa-sign-in-alt ms-1"></i>
                                                    </button>
                                                </div>
                                            </div><!--end col-->
                                        </div>
                                    </form><!--end form-->
                                </div><!--end card-body-->
                            </div><!--end card-->
                        </div><!--end col-->
                    </div><!--end row-->
                </div><!--end card-body-->
            </div><!--end col-->
        </div><!--end row-->
    </div>

    <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="termsModalLabel">Confidentiality Agreement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
                    <h5>NON-DISCLOSURE AGREEMENT</h5>
                    <p>This Agreement is made by and between ________________, (“Company”), and Broker, a WY corporation
                        whose principal mailing address is 677 King Street Charleston SC 29403(“Recipient”).
                    </p>
                    <ol>
                        <li><strong>Definition of Confidentiality.</strong>As used in this Agreement, "Confidential
                            Information" refers to any information which has commercial value and is either (i) technical
                            information, including patent, copyright, trade secret, and other proprietary information,
                            techniques, sketches, drawings, models, inventions, know-how, processes, apparatus, equipment,
                            algorithms, software programs, software source documents, and formulae related to the current,
                            future and proposed products and services of Company, or (ii) non-technical information relating
                            to Company's products, including without limitation pricing, margins, merchandising plans and
                            strategies, finances, financial and accounting data and information, suppliers, customers,
                            customer lists, purchasing data, sales and marketing plans, future business plans and any other
                            information which is proprietary and confidential to Company./li>
                        <li><strong>No Copying/Modifying.</strong> The Recipient will not copy or modify any Confidential
                            Information without
                            the prior written consent of the Seller.
                        </li>
                        <li><strong>Nondisclosure and Nonuse Obligations.</strong>The recipient will maintain confidence and
                            will not disclose, disseminate or use any Confidential Information belonging to Company, whether
                            or not in written form. Recipient agrees that Recipient shall treat all Confidential Information
                            of Company with at least the same degree of care as Recipient accords its own confidential
                            information. Recipient further represents that Recipient exercises at least reasonable care to
                            protect its own confidential information. If Recipient is not an individual, Recipient agrees
                            that Recipient shall disclose Confidential Information only to those of its employees who need
                            to know such information, and certifies that such employees have previously signed a copy of
                            this Agreement.
                        </li>
                        <li><strong>Survival.</strong>This Agreement shall govern all communications between the parties.
                            Recipient understands that its obligations under Paragraph 2 ("Nondisclosure and Nonuse
                            Obligations") shall survive the termination of any other relationship between the parties. Upon
                            termination of any relationship between the parties, Recipient will promptly deliver to Company,
                            without retaining any copies, all documents and other materials furnished to Recipient by
                            Company.
                        </li>
                        <li><strong>Governing Law.</strong> This Agreement shall be governed in all respects by the laws of
                            the United States of America and by the laws of the State the Company resides in. </li>
                        <li><strong>Injunctive Relief.</strong> A breach of any of the promises or agreements contained
                            herein will result in irreparable and continuing damage to Company for which there will be no
                            adequate remedy at law, and Company shall be entitled to injunctive relief and/or a decree for
                            specific performance, and such other relief as may be proper (including monetary damages if
                            appropriate).
                        </li>
                        <li><strong>Entire Agreement.</strong> This Agreement constitutes the entire agreement with respect
                            to the Confidential Information disclosed herein and supersedes all prior or contemporaneous
                            oral or written agreements concerning such Confidential Information. This Agreement may only be
                            changed by mutual agreement of authorized representatives of the parties in writing.
                        </li>
                    </ol>
                    <p> IN WITNESS WHEREOF, the parties have executed this Agreement as of the date first written below.</p>
                </div>
                <div class="modal-footer">
                    <input type="checkbox" id="agreeTerms">
                    <label for="agreeTerms">I agree to the Terms & Conditions</label>
                    <button type="button" class="btn btn-primary" id="acceptTermsBtn" disabled
                        data-bs-dismiss="modal">Accept</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var termsModalBody = document.querySelector(".modal-body");
            var agreeCheckbox = document.getElementById("agreeTerms");
            var acceptButton = document.getElementById("acceptTermsBtn");
            var mainTermsCheckbox = document.getElementById("termsCheckbox");
            var registerButton = document.getElementById("registerBtn");
    
            // Initially disable the accept button and checkbox
            agreeCheckbox.disabled = true;
            acceptButton.disabled = true;
    
            // Listen for scroll event on the modal body
            termsModalBody.addEventListener("scroll", function () {
                if (termsModalBody.scrollTop + termsModalBody.clientHeight >= termsModalBody.scrollHeight - 5) {
                    // Enable the checkbox once scrolled to bottom
                    agreeCheckbox.disabled = false;
                }
            });
    
            // Enable the Accept button only when the checkbox is checked
            agreeCheckbox.addEventListener("change", function () {
                acceptButton.disabled = !this.checked;
            });
    
            // When "Accept" button is clicked, check the main terms checkbox and enable the register button
            acceptButton.addEventListener("click", function () {
                mainTermsCheckbox.checked = true;
                mainTermsCheckbox.disabled = false; // Enable it for visual clarity
                registerButton.disabled = false;
            });
    
            // Prevent form submission if the terms checkbox is not checked
            document.querySelector("form").addEventListener("submit", function (event) {
                if (!mainTermsCheckbox.checked) {
                    event.preventDefault();
                    alert("You must agree to the Terms of Use before proceeding.");
                }
            });
        });
    </script>
@endsection
