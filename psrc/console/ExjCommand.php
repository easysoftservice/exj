<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

class ExjCommand extends Command {
	private $_input=null;
	private $_output=null;

	protected function initialize(InputInterface $input, OutputInterface $output)
    {
    	$this->_input = $input;
    	$this->_output = $output;
    	parent::initialize($input, $output);

        $selfCmd = $this;
        ExjEvent::On('setError', function($scope, $msgError, $typeError) use($selfCmd) {
            $selfCmd->error(
                ExjError::GetTextTypeError($typeError, true).'. '.$msgError
            );
        }, Exj::class);
    }

    protected function getOutput(){
        return $this->_output;
    }

    public function line($value, $style = null) {
        $styled = $style ? "<$style>$value</$style>" : $value;

        $this->getOutput()->writeln($styled);
        return $this;
    }

    public function comment($string) {
        return $this->line($string, 'comment');
    }

    public function writeQuestion($string) {
        return $this->line($string, 'question');
    }

    public function info($string) {
        return $this->line($string, 'info');
    }

    public function error($string) {
        return $this->line($string, 'error');
    }

    public function warn($string) {
        $output = $this->getOutput();

        if (! $output->getFormatter()->hasStyle('warning')) {
            $style = new OutputFormatterStyle('green', 'red');

            $output->getFormatter()->setStyle('warning', $style);
        }

        return $this->line($string, 'warning');
    }

    public function alert($string)
    {
        $this->comment(str_repeat('*', strlen($string) + 12));
        $this->comment('*     '.$string.'     *');
        $this->comment(str_repeat('*', strlen($string) + 12));

        $this->getOutput()->writeln('');
        return $this;
    }


	protected function writeInfo($msg){
		return $this->write("<info>$msg</info>");
	}

	protected function writeLnInfo($msg){
		return $this->info($msg);
	}

	protected function writeLnComment($msg){
		return $this->comment($msg);
	}

    protected function writeComment($msg){
        return $this->write("<comment>$msg</comment>");
    }

	protected function writeLnQuestion($msg){
		return $this->writeQuestion($msg);
	}

	protected function writeError($msg){
		return $this->write("<error>$msg</error>");
	}

	protected function writeLnError($msg){
		return $this->error($msg);
	}

	protected function write($msg){
		$this->_output->write($msg);
		return $this;
	}

	protected function writeln($msg){
		$this->_output->writeln($msg);
		return $this;
	}


	protected function getExj(){
		return $this->getApplication()->_exj;
	}

    public function haveError() {
        return $this->getExj()->haveError();
    }

	protected function loginUser($username, $password) {
        $mainframe = $this->getExj()->getMainframe();

        $mainframe->login([
            'username' => $username,
            'password' => $password,
        ], [
            'silent' => true
        ]);

        $user = JFactory::getUser();

        if (!$user->id) {
            $this->write(
            	"Usuario: <error>$username</error> no auntenticado. DB: ".
            	   ExjDatabase::GetNameDB()
            );
            exit();
        }

        if ($user->block >= 1) {
            $this->write("Usuario: <error>$username</error> esta bloqueado");
            exit();
        }


        $infoUser = AppGlobalModel::getDataInfoUser();
        $hInfoUser = new ExjHelperInfoUser();
        $hInfoUser->bindToSession($infoUser);
        return $this;
    }

    protected function getHelperQuestion(){
        return $this->getHelper('question');
    }

    public function confirm($msg, $default = false){
        $question = new ConfirmationQuestion(
        	"<question>$msg?</question> (y/s/n) ",
        	$default,
        	'/^(y|s)/i'
        );

        return $this->ask($question, $default);
    }

    public function ask($question, $default = null) {
        return $this->getHelperQuestion()->ask(
            $this->_input, $this->_output, $question, $default
        );
    }

    public function question($question, $default = null, $valuesAutoComp=null) {
        $question = new Question($question, $default);

        if (!empty($valuesAutoComp)) {
            $question->setAutocompleterValues($valuesAutoComp);
        }

        return $this->ask($question);
    }

    public function choice($question, array $choices, $default = null, $isMulSel = null) {
        $question = new ChoiceQuestion($question, $choices, $default);
        $question->setMultiselect($isMulSel);
        $question->setErrorMessage('Valor "%s" es invalido.');

        return $this->ask($question);
    }

    public function choiceMultiSelect($question, array $choices, $default = null) {
        return $this->choice($question, $choices, $default, true);
    }
}
